<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-05-26
 */

namespace App\Controller;

use App\Entity\Balance;
use App\Entity\Order;
use App\Entity\OrderElement;
use App\Entity\Product;
use App\Repository\OrderElementRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Util\ArrayUtil;
use App\Util\StatusUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OrderController
 * @package App\Controller
 *
 * @Route("/orders")
 */
class OrderController extends BaseController
{
    /**
     * @Route("/", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index(EntityManagerInterface $em)
    {
        /** @var Order[] $orders */
        $orders = $em->getRepository(Order::class)->findOrders();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $orders,
        ]);
    }

    /**
     * @Route("/{order}", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @param $order
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(EntityManagerInterface $em, $order)
    {
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $order,
        ]);
    }

    /**
     * @Route("/{order}/products")
     *
     * @param EntityManagerInterface $em
     * @param $order integer
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getProducts(EntityManagerInterface $em, $order)
    {
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);
        if (is_null($order)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la orden para encontrar los artículos',
            ]);
        }

        $products = [];
        /** @var ProductRepository $productsRepository */
        $productsRepository = $em->getRepository(Product::class);
        /** @var OrderElement $element */
        foreach ($order->getElements()->getIterator() as $element) {
            $product = $productsRepository->find($element->getParentId());
            $products[] = [
                'id'        => $product->getId(),
                'name'      => $product->getName(),
                'qty'       => $element->getQty(),
                'price'     => $product->getPrice(),
                'amount'    => $element->getAmount(),
                'interest'  => $element->getInterest(),
            ];
        }

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $products,
        ]);
    }

    /**
     * @Route("/", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createAction(EntityManagerInterface $em, LoggerInterface $logger, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $discount = ArrayUtil::safe($data, 'discount', null);
        if (is_null($discount)) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'The field discount is required',
            ]);
        }

        // In order to start transaction
        /*
         * **********************
         * BEGIN TRANSACTION
         * **********************
         */
        $em->beginTransaction();

        $order = new Order();
        $order
            ->setDiscount($discount)
            ->setInterest(0)
        ;
        $em->persist($order);

        // In case that create an order without elements
        // Because specification provided orders without products until order is completed
//        if (!array_key_exists('products', $data) || 0 == count($data['products'])) {
//            /*
//             * **********************
//             * ROLLBACK
//             * **********************
//             */
//            $em->rollback();
//
//            return $this->json([
//                'code'  => Response::HTTP_BAD_REQUEST,
//                'error' => 'Field products are required and need at lest one'
//            ]);
//        }
        $products = ArrayUtil::safe($data, 'products', []);

        /** @var ProductRepository $productsRepository */
        $productsRepository = $em->getRepository(Product::class); // Allocate once to avoid overhead
        $total = 0;
        foreach ($products as $element) {
            /** @var Product $product */
            $product = $productsRepository->find($element['id']);
            if (is_null($product)) {
                /*
                 * **********************
                 * ROLLBACK
                 * **********************
                 */
                $em->rollback();

                return $this->json([
                    'code'  => Response::HTTP_BAD_REQUEST,
                    'error' => "There's no product find in order elements"
                ]);
            }
            if ($product->getQty() < $element['qty']) {
                /*
                 * **********************
                 * ROLLBACK
                 * **********************
                 */
                $em->rollback();

                return $this->json([
                    'code'  => 400,
                    'error' => "El inventario del producto {$product->getName()} no satisface la cantidad solicitada"
                ]);
            }

            /** @var OrderElement $orderElement */
            $orderElement = new OrderElement();
            $orderElement
                ->setParentClass(get_class($product))
                ->setParentId($product->getId())
                ->setQty($element['qty'])
                ->setAmount($product->getPrice() * $element['qty'])
                ->setLabel($product->getName())
            ;
            $order->addElement($orderElement);

            // In order to discount from inventorys
            $product->setQty($product->getQty() - $element['qty']);
            $em->persist($product);

            $em->persist($orderElement);
        }

        $em->persist($order);
        $em->flush();

        /*
         * **********************
         * COMMIT
         * **********************
         */
        $em->commit();

        /*
         * **********************
         * BEGIN TRANSACTION
         * **********************
         */
        $em->beginTransaction();

        try {
            // Balance
            $balance = new Balance();
            $balance
                ->setParentClass(get_class($order))
                ->setParentId($order->getId())
                ->setAmount($order->getTotal())
            ;

            // In order to check that is payed
            $paid = ArrayUtil::safe($data, 'paid', false);
            if ((bool) $paid) {
                $order->addStatus(StatusUtil::PAID);
                $balance->setAmount(0);
            }

            $em->persist($order);
            $em->persist($balance);
            $em->flush();

        } catch (\Exception $exception) {
            /*
             * **********************
             * ROLLBACK
             * **********************
             */
            $em->rollback();
        }

        /*
         * **********************
         * COMMIT
         * **********************
         */
        $em->commit();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => $order
        ]);
    }

    /**
     * @Route("/{order}/addElements", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $order int
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function addOrderElements(EntityManagerInterface $em, LoggerInterface $logger, Request $request, $order)
    {
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);
        if (is_null($order)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la orden para agregar para agregar los artículos'
            ]);
        }
        $data = json_decode($request->getContent(), true);
        $products = ArrayUtil::safe($data, 'products', []);
        $logger->debug(var_export($products, true));

        $err = false;
        $errMessage = '';

        /**
         * ******************************
         * BEGIN TRANSACTION
         * ******************************
         */
        $em->beginTransaction();

        $elementsAmount = 0.00; // Use to track new elements of order and aggregate to original order balance
        /** @var ProductRepository $productsRepository */
        $productsRepository = $em->getRepository(Product::class); // Once
        /** @var OrderElementRepository $orderElementRepository */
        $orderElementRepository = $em->getRepository(OrderElement::class); // Once
        foreach ($products as $element) {
            /** @var Product $product */
            $product = $productsRepository->find(ArrayUtil::safe($element, 'id', -1));

            if (is_null($product)) { // Product doesn't exists
                $err = true;
                $errMessage = 'No se ha encontrado un artículo para agregar a la orden';

                break;
            }

            if ($product->getQty() < $element['qty']) {
                $err = true;
                $errMessage = "La cantidad de artículos para el producto {$product->getName()} supera el inventario";

                break;
            }

            $orderAmount = $product->getPrice() * $element['qty'];
            $orderAmountInterest = $orderAmount * ($product->getInterest() / 100);
            $elementsAmount += $orderAmount + $orderAmountInterest; // In order to update balance of order

            // Try to find order element to avoid duplicate it, just update it
            /** @var OrderElement $orderElement */
            $orderElement = $orderElementRepository->findByParent($order, get_class($product), $product->getId());
            if (!is_null($orderElement)) {
                $orderElement
                    ->addQty($element['qty'])
                    ->setInterest($orderAmountInterest)
                    ->addAmount($orderAmount + $orderAmountInterest)
                ;
                $order
                    ->addToTotal($orderAmount + $orderAmountInterest)
                    ->addToInterest($orderAmountInterest)
                ;

            } else {
                /** @var OrderElement $orderElement */
                $orderElement = new OrderElement();
                $orderElement
                    ->setParentClass(get_class($product))
                    ->setParentId($product->getId())
                    ->setQty($element['qty'])
                    ->setInterest($orderAmountInterest)
                    ->addAmount($orderAmount + $orderAmountInterest)
                    ->setLabel($product->getName())
                ;
                $order->addElement($orderElement);
            }

            // In order to discount from inventorys
            $product->setQty($product->getQty() - $element['qty']);
            $em->persist($orderElement);
            $em->persist($product);
            $em->persist($order);
        }

        if ($err) {
            /**
             * ******************************
             * ROLLBACK
             * ******************************
             */
            $em->rollback();

            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => $errMessage,
            ]);
        }

        $em->flush();
        /**
         * ******************************
         * COMMIT
         * ******************************
         */
        $em->commit();

        // New transaction to avoid losing data about elements in order
        /**
         * ******************************
         * BEGIN TRANSACTION
         * ******************************
         */
        $em->beginTransaction();
        /** @var Balance $balance */
        $balance = $em->getRepository(Balance::class)->findByParent(get_class($order), $order->getId());
        if (is_null($balance)) {
            $balance = new Balance();
            $balance
                ->setParentClass(get_class($order))
                ->setParentId($order->getId())
            ;
        }

        $balance->addBalance($elementsAmount);

        $em->persist($balance);
        $em->flush();
        /**
         * ******************************
         * COMMIT
         * ******************************
         */
        $em->commit();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => $order
        ]);
    }

    /**
     * @Route("/{order}/status")
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $order
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addStatus(EntityManagerInterface $em, Request $request, $order)
    {
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);
        if (is_null($order)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la orden',
            ]);
        }
        $data = json_decode($request->getContent(), true);
        $status = ArrayUtil::safe($data, 'status', StatusUtil::PENDING);

        /**
         * ********************
         * BEGIN TRANSACTION
         * ********************
         */
        $em->beginTransaction();

        $order->addStatus($status);

        $em->persist($order);
        $em->flush();

        /**
         * ********************
         * COMMIT
         * ********************
         */
        $em->commit();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $order,
        ]);
    }
}