<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 2019-06-02
 */

namespace App\Model;

use App\Entity\Balance;
use App\Entity\Movement;
use App\Entity\Order;
use App\Exception\BusinessLogicException;
use App\Repository\OrderRepository;
use App\Util\ArrayUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DelayedPaymentModel
 * @package App\Model
 */
class DelayedPaymentModel extends AbstractModel
{
    /**
     * @var Movement
     */
    protected $entity;

    /**
     * @var OrderRepository
     */
    private $ordersRepository;

    /**
     * DelayedPaymentModel constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, RequestStack $requestStack)
    {
        parent::__construct($em, $logger, $requestStack);
        $this->ordersRepository = $this->em->getRepository(Order::class);
    }

    /**
     * @param Movement $movement
     * @return DelayedPaymentModel
     */
    public function setEntity($entity): AbstractModel
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return mixed|void
     * @throws BusinessLogicException
     */
    public function process()
    {
        /** @var array $request */
        $request = json_decode($this->requestStack->getCurrentRequest()->getContent(), true);
        /** @var float $amount */
        $amount = ArrayUtil::safe($request, 'amount', 0.00);
        if (0 >= $amount) {
            throw new BusinessLogicException('El monto es obligatorio para procesar el movimiento', 400);
        }
        $this->updateBalance($amount);
        $info = [
            'amount'    => (float) $amount,
            'comment'   => ArrayUtil::safe($request, 'comment', '')
        ];
        $this->entity->setInfo(json_encode($info));
        $this->em->persist($this->entity);
    }

    /**
     * Update balance of the order. Do something when balance is zero?
     *
     * @throws BusinessLogicException
     */
    private function updateBalance($amount)
    {
        /** @var Order $order */
        $order = $this->ordersRepository->find($this->entity->getParentId());
        if (is_null($order)) {
            throw new BusinessLogicException('Movimiento con padre vacío', 412);
        }

        /** @var Balance $balance */
        $balance = $this->em->getRepository(Balance::class)->findOneBy([
            'parentId'      => $order->getId(),
            'parentClass'   => get_class($order)
        ]);

        if (is_null($balance)) {
            throw new BusinessLogicException('Se ha generado la orden sin balance', 412);
        }

        if ($balance->getAmount() < $amount) {
            throw new BusinessLogicException('El monto del movimiento excede el total de la cuenta', 400);
        }

        $balance
            ->setAmount($balance->getAmount() -  $amount)
            ->setUpdatedAt(new \DateTime('now'))
        ;
        $order->setUpdatedAt(new \DateTime('now'));
        $this->em->persist($balance);
        $this->em->persist($order);
    }
}