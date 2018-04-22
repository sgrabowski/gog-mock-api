<?php

namespace App\Listener;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof ValidationException)) {
            return;
        }

        $code = 400;

        $responseData = [
            "message" => "Validation failed",
            'validationErrors' => $this->formatViolationList($exception->getValidationErrors())
        ];

        $event->setResponse(new JsonResponse($responseData, $code));
    }

    /**
     * Returns formatted errors
     *
     * @return array
     */
    protected function formatViolationList(ConstraintViolationListInterface $violations)
    {
        $errors = [];

        foreach ($violations as $validationError) {
            $propertyPath = $validationError->getPropertyPath();
            $message = $validationError->getMessage();
            $errors[$propertyPath] = $message;
        }

        return $errors;
    }
}