<?php

namespace App\Validator;

use App\DataManager\ProductManagerInterface;
use App\DTO\ProductDTO;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueProductTitleValidator extends ConstraintValidator
{
    private $productManager;

    public function __construct(ProductManagerInterface $productManager)
    {
        $this->productManager = $productManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueProductTitle) {
            throw new UnexpectedTypeException($constraint, UniqueProductTitle::class);
        }
        if (!$value instanceof ProductDTO) {
            throw new UnexpectedTypeException($value, ProductDTO::class);
        }

        if($this->productManager->exists($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ title }}', $value->title)
                ->atPath("title")
                ->addViolation();
        }
    }
}