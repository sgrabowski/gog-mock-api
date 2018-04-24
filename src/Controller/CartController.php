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
use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * Add product to the cart
     *
     * @Rest\Put("/carts/{cartId}/products/{productId}")
     * @Rest\View(statusCode=200)
     * @Rest\QueryParam(name="quantity", requirements=@Assert\Range(min=0, max=10), default=1, description="page number", strict=true )
     *
     * @return CartDTO
     */
    public function updateProductAction($cartId, $productId, $quantity)
    {
        return $this->manager->setProduct($cartId, $productId, $quantity);
    }
}