<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{

    private $validationErrors;

    public function __construct(ConstraintViolationListInterface $validationErrors)
    {
        $this->validationErrors = $validationErrors;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }

}
