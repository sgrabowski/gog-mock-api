<?php

namespace App\DataManager;

class ObjectNotFoundException extends \Exception
{
    public function __construct($message = "Object not found", $code = 400)
    {
        parent::__construct($message, $code);
    }
}