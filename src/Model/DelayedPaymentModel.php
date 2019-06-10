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
use App\Entity\OrderElement;
use App\Entity\Product;
use App\Entity\Provider;
use App\Exception\BusinessLogicException;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
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
     * @var ProductRepository
     */
    private $productsRepository;

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
        $this->productsRepository = $this->em->getRepository(Product::class);
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

        /**
         * In case that balance is zero os the total of the account
         */
        if (0 == $balance->getAmount()) {
            $elements = $order->getElements();
            /** @var OrderElement $element */
            foreach ($elements as $element) {
                // Search products
                if ($element->getParentClass() === Product::class) {
                    $product = $this->productsRepository->find($element->getParentId());
                    if (is_null($product)) {
                        throw new BusinessLogicException('No se ha encontrado el producto de la orden');
                    }

                    $this->updateProviderBalance($product->getProvider(), $product);
                }
            }
        }

        $order->setUpdatedAt(new \DateTime('now'));
        $this->em->persist($balance);
        $this->em->persist($order);
    }

    /**
     * Use to update balance just in case that movement is totally fund
     *
     * @param Provider $provider
     * @param Product $product
     * @throws BusinessLogicException
     */
    private function updateProviderBalance(Provider $provider, Product $product)
    {
        if (is_null($provider)) {
            throw new BusinessLogicException('No se ha encontrado proveedor para el producto vendido.', 412);
        }
        /** @var Balance $balance */
        $balance = $this->em->getRepository(Balance::class)->findOneBy([
            'parentClass'   => Provider::class,
            'parentId'      => $provider->getId(),
        ]);
        if (is_null($balance)) {
            throw new BusinessLogicException('No se ha encontrado el balance del proveedor', 412);
        }
        $balance->addBalance($product->getPrice());
        $this->em->persist($balance);
    }
}