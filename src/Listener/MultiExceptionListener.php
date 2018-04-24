<?php

namespace App\Listener;

use App\DataManager\FullCartException;
use App\DataManager\ObjectNotFoundException;
use App\DataManager\ProductNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class MultiExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $code = null;
        $message = null;

        if ($exception instanceof ObjectNotFoundException) {
            $code = 404;
            $message = $exception->getMessage();
        }

        if ($exception instanceof ProductNotFoundException) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        }

        if ($exception instanceof FullCartException) {
            $code = 400;
            $message = $exception->getMessage();
        }

        //exception was not handled
        if($code === null && $message === null) {
            return;
        }

        $responseData = [
            "message" => $message,
            "code" => $code
        ];

        $event->setResponse(new JsonResponse($responseData, $code));
    }
}