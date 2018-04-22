<?php

namespace App\Controller;

use App\DataManager\CartManagerInterface;
use App\DTO\CartDTO;
use App\DTO\CurrencyDTO;
use App\Exception\ValidationException;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

class CartController extends FOSRestController
{
    private $manager;

    public function __construct(CartManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Creates an empty cart
     *
     * @Rest\Post("/carts")
     * @Rest\View(statusCode=201)
     * @ParamConverter("currencyDTO", converter="fos_rest.request_body")
     *
     * @return CartDTO
     * @throws ValidationException
     */
    public function createAction(CurrencyDTO $currencyDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        return $this->manager->create($currencyDTO);
    }
}