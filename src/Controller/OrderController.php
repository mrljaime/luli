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
        if (!array_key_exists('products', $data) || 0 == count($data['products'])) {
            /*
             * **********************
             * ROLLBACK
             * **********************
             */
            $em->rollback();

            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'Field products are required and need at lest one'
            ]);
        }
        $products = $data['products'];

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
                    'error' => 'Not enough elements qty products to satisfied order'
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
}