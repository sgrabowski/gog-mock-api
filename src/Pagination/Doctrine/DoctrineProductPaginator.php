<?php

namespace App\Pagination\Doctrine;

use App\DTO\ProductDTO;
use App\Pagination\PageNotFoundException;
use App\Pagination\PaginatorInterface;
use App\Repository\ProductRepository;
use AutoMapperPlus\AutoMapperInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;

class DoctrineProductPaginator implements PaginatorInterface
{
    private $fantaPaginator;
    private $mapper;

    public function __construct(ProductRepository $repository, AutoMapperInterface $mapper)
    {
        $adapter = new DoctrineORMAdapter($repository->getListQueryBuilder());
        $this->fantaPaginator = new Pagerfanta($adapter);
        $this->mapper = $mapper;
    }

    public function setMaxPerPage($maxPerPage): PaginatorInterface
    {
        $this->fantaPaginator->setMaxPerPage($maxPerPage);

        return $this;
    }

    public function setCurrentPage($currentPage): PaginatorInterface
    {
        try {
            $this->fantaPaginator->setCurrentPage($currentPage);
        } catch (OutOfRangeCurrentPageException $e) {
            throw new PageNotFoundException();
        }

        return $this;
    }

    public function getMaxPerPage(): int
    {
        return $this->fantaPaginator->getMaxPerPage();
    }

    public function getCurrentPage(): int
    {
        return $this->fantaPaginator->getCurrentPage();
    }

    public function getCurrentPageResults()
    {
            return $this->mapper->mapMultiple($this->fantaPaginator->getCurrentPageResults(), ProductDTO::class);
    }

    public function getTotalResults()
    {
        return $this->fantaPaginator->getNbResults();
    }

    public function getTotalPages()
    {
        return $this->fantaPaginator->getNbPages();
    }
}