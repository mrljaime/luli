<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 * @version 1
 * @since 2019-06-02
 */

namespace App\Model;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Use to init all basic stuff to execute business logic
 *
 * Class AbstractModel
 * @package App\Model
 */
abstract class AbstractModel
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    // Without type declaration because needs to be override
    protected $entity;

    /**
     * AbstractModel constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    /**
     * @param $entity
     * @return AbstractModel
     */
    public abstract function setEntity($entity): self;

    /**
     * @return mixed
     */
    public abstract function process();

    /**
     * @return bool
     */
    protected function isReady()
    {
        return (null != $this->entity);
    }
}