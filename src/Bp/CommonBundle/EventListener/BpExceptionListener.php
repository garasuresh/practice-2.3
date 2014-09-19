<?php
/**
 * Created by JetBrains PhpStorm.
 * User: suresh
 * Date: 19/09/14
 * Time: 09:41
 * To change this template use File | Settings | File Templates.
 */

namespace Bp\CommonBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
class BpExceptionListener {

    private $templateEngine;

    public function __construct( EngineInterface $templateEngine ){
        $this->templateEngine = $templateEngine;
    }

    public function onKernelException(GetResponseForExceptionEvent $event){
        // You get the exception object from the received event
        /** @var  $exception \Exception */
        $exception = $event->getException();

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent(
            $this->templateEngine->render(
                'BpCommonBundle:Exception:exception-404.html.twig',array('exception' => $exception)
            )
        );

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(500);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }
}