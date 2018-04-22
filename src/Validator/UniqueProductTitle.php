<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueProductTitle extends Constraint
{
    public $message = 'Product "{{ title }}" already exists';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}