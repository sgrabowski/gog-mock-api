<?php

namespace App\Pagination;

interface PaginatorInterface
{
    public function setMaxPerPage($maxPerPage): PaginatorInterface;
    public function setCurrentPage($currentPage): PaginatorInterface;
    public function getMaxPerPage(): int;
    public function getCurrentPage(): int;
    public function getCurrentPageResults();
    public function getTotalResults();
    public function getTotalPages();
}