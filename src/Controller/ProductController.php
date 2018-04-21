<?php

namespace App\Controller;

use App\DataManager\ProductManagerInterface;
use App\DTO\ProductDTO;
use App\Exception\ValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProductController extends FOSRestController
{
    private $manager;

    public function __construct(ProductManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Creates a product
     *
     * @Rest\Post("/products")
     * @Rest\View(statusCode=201)
     * @ParamConverter("productDTO", converter="fos_rest.request_body")
     *
     * @return ProductDTO
     * @throws ValidationException
     */
    public function createAction(ProductDTO $productDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        return $this->manager->create($productDTO);
    }
}