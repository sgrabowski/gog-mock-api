<?php

namespace App\Controller;

use App\DataManager\ObjectNotFoundException;
use App\DataManager\ProductManagerInterface;
use App\DTO\PriceDTO;
use App\DTO\ProductDTO;
use App\DTO\UpdateProductDTO;
use App\Exception\ValidationException;
use App\Pagination\PageNotFoundException;
use App\Pagination\PaginatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * Partially updates a product
     *
     * Only product's fields can be changed, not related objects such as prices
     *
     * @Rest\Patch("/products/{id}")
     * @Rest\View(statusCode=200)
     * @ParamConverter("updateProductDTO", converter="fos_rest.request_body")
     *
     * @return ProductDTO
     * @throws ValidationException
     */
    public function updateAction($id, UpdateProductDTO $updateProductDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        try {
            return $this->manager->update($id, $updateProductDTO);
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Replaces a product
     *
     * Object is replaced along with all relations, such as prices
     *
     * @Rest\Put("/products/{id}")
     * @Rest\View(statusCode=200)
     * @ParamConverter("productDTO", converter="fos_rest.request_body")
     *
     * @return ProductDTO
     * @throws ValidationException
     */
    public function replaceAction($id, ProductDTO $productDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        try {
            return $this->manager->replace($id, $productDTO);
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Updates product's price
     *
     * If the given price doesn't exist, it's created.
     * Otherwise it's replaced
     *
     * @Rest\POST("/products/{id}/prices")
     * @Rest\View(statusCode=200)
     * @ParamConverter("priceDTO", converter="fos_rest.request_body")
     *
     * @return ProductDTO
     * @throws ValidationException
     */
    public function updatePriceAction($id, PriceDTO $priceDTO, ConstraintViolationListInterface $validationErrors)
    {
        if ($validationErrors->count() > 0) {
            throw new ValidationException($validationErrors);
        }

        try {
            return $this->manager->setPrice($id, $priceDTO);
        } catch (ObjectNotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Lists products
     *
     * @Rest\Get("/products")
     * @Rest\View(statusCode=200)
     *
     * @Rest\QueryParam(name="page", requirements=@Assert\GreaterThan(0), default=1, description="page number", strict=true )
     * @Rest\QueryParam(name="limit", requirements=@Assert\Collection({@Assert\GreaterThan(0),@Assert\LessThan(100)}), default=3, description="items per page", strict=true )
     *
     * @return View
     */
    public function listAction($page, $limit)
    {
        try {
            $paginator = $this->manager->getPaginator($page, $limit);
        } catch (PageNotFoundException $e) {
            throw new NotFoundHttpException();
        }

        return $this->view($paginator->getCurrentPageResults(), 200, [
            "X-pagination-total-results" => $paginator->getTotalResults(),
            "X-pagination-total-pages" => $paginator->getTotalPages()
        ]);
    }
}