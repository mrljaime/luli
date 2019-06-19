<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 1
 */

namespace App\Controller;

use App\Entity\Movement;
use App\Entity\Order;
use App\Exception\BusinessLogicException;
use App\Model\AbstractModel;
use App\Model\DelayedPaymentModel;
use App\Util\ArrayUtil;
use App\View\AbstractView;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintValidatorInterface;

/**
 * Class MovementsController
 * @package App\Controller
 * @Route("/movements")
 */
class MovementsController extends BaseController
{
    private $movements = [];

    /**
     * MovementsController constructor.
     * @param DelayedPaymentModel $delayedPaymentModel
     */
    public function __construct(DelayedPaymentModel $delayedPaymentModel)
    {
        $this->movements['delayedPayment'] = $delayedPaymentModel;
    }

    /**
     * @Route("/order/{order}", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param $order
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrderAction(EntityManagerInterface $em, Request $request, $order)
    {
        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);
        if (is_null($order)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la orden solicitada',
            ]);
        }

        /** @var Movement[] $movements */
        $movements = $em->getRepository(Movement::class)->findBy(
            [
                'parentClass'   => get_class($order),
                'parentId'      => $order->getId(),
            ],
            [
                'createdAt'     => 'DESC',
            ]
        );

        $data = [];
        /** @var Movement $movement */
        foreach ($movements as $movement) {
            $view = AbstractView::factory('delayedPayment', $movement->jsonSerialize());
            $data[] = $view->buildView();
        }

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $data,
        ]);
    }

    /**
     * @Route("/order/{order}", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param ConstraintValidatorInterface $validator
     * @param Request $request
     * @param $type string
     * @param $order integer
     */
    public function addOrderAction(EntityManagerInterface $em, LoggerInterface $logger, Request $request, $order)
    {
        $request = json_decode($request->getContent(), true);
        $type = ArrayUtil::safe($request, 'type', null);
        if (is_null($type) || 0 == (strlen(trim($type)))) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'El tipo de movimiento es obligatorio'
            ]);
        }

        /** @var Order $order */
        $order = $em->getRepository(Order::class)->find($order);
        if (is_null($order)) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'No se ha encontrado la orden'
            ]);
        }

        $movement = new Movement();
        $movement
            ->setParentClass(get_class($order))
            ->setParentId($order->getId())
            ->setType(Movement::TYPES[$type])
        ;

        /**
         * ***************
         * BEGIN TRANSACTION
         * ***************
         */
        $em->beginTransaction();

        try {
            $em->persist($movement);

            /** @var AbstractModel $model */
            $model = $this->movements[$type];
            $model
                ->setEntity($movement)
                ->process()
            ;

            $em->flush();

        } catch (BusinessLogicException $exception) {
            /**
             * ROLLBACK
             */
            $em->rollback();
            $logger->error(sprintf('%s - %s', $exception->getMessage(), $exception->getTraceAsString()));

            if (Response::HTTP_PRECONDITION_FAILED === $exception->getCode()) {
                return $this->json([
                    'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'error' =>'Hubo un error de negocio. Contacte a un administrador',
                ]);
            }

            return $this->json([
                'code'  => $exception->getCode(),
                'error' => $exception->getMessage(),
            ]);

        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());

            /**
             * ***************
             * ROLLBACK
             * ***************
             */
            $em->rollback();

            return $this->json([
                'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => 'Hubo un error interno. Contacte a un administrador',
            ]);
        }

        /**
         * ***************
         * COMMIT
         * ***************
         */
        $em->commit();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => 'Movimiento creado con éxito',
        ]);
    }
}