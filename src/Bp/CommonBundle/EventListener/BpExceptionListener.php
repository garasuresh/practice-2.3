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

class BpExceptionListener {
    public function onKernelException(GetResponseForExceptionEvent $event){
        // You get the exception object from the received event
        /** @var  $exception \Exception */
        $exception = $event->getException();

        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getStatusCode()
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

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