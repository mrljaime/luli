<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-03-06
 */

namespace App\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiCorsListener
 * @package App\EventListener
 */
class ApiCorsListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->debug('Cosntructor');
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE  => 'onResponse',
            KernelEvents::REQUEST   => ['onRequest', 250],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ('OPTIONS' === $request->getMethod()) {
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
            $response->headers->set('Access-Control-Allow-Headers', 'X-Custom-Auth,content-type');
            $event->setResponse($response);

            return;
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->add([
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}