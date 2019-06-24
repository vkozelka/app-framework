<?php

namespace App\System;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;

class Pagination
{

    /**
     * @var QueryBuilder
     */
    protected $_query;

    protected $_page = 1;

    protected $_perPage = 10;
    
    protected $_totalRecords = 0;

    protected $_totalPages = 0;

    protected $_nextPage = 0;

    protected $_previousPage = 0;

    protected $_firstPage = 0;

    protected $_lastPage = 0;

    
    private $__calculatedResults = false;

    public function __construct(QueryBuilder $query, $page = 1, $perPage = 10)
    {
        App::get()->getProfiler()->start("App::Pagination");
        $this->setQuery($query);
        $this->setPage($page);
        $this->setPerPage($perPage);
        App::get()->getProfiler()->stop("App::Pagination");
    }

    /**
     * @return QueryBuilder
     */
    public function getQuery(): QueryBuilder
    {
        return $this->_query;
    }

    /**
     * @param QueryBuilder $query
     * @return Pagination
     */
    public function setQuery(QueryBuilder $query): Pagination
    {
        $this->_query = $query;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->_page;
    }

    /**
     * @param int $page
     * @return Pagination
     */
    public function setPage(int $page): Pagination
    {
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        $this->_page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->_perPage;
    }

    /**
     * @param int $perPage
     * @return Pagination
     */
    public function setPerPage(int $perPage): Pagination
    {
        if (!is_numeric($perPage) || $perPage < 1) {
            $perPage = 10;
        }
        $this->_perPage = $perPage;
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getPaginatedQuery($fresh = false): QueryBuilder
    {
        if (!$this->__calculatedResults || $fresh == true) {
            $this->__calculateResults();
        }
        return $this->getQuery();
    }

    /**
     * @return int
     */
    public function getTotalRecords(): int
    {
        return $this->_totalRecords;
    }

    /**
     * @param int $totalRecords
     * @return Pagination
     */
    public function setTotalRecords(int $totalRecords): Pagination
    {
        $this->_totalRecords = $totalRecords;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->_totalPages;
    }

    /**
     * @param int $totalPages
     * @return Pagination
     */
    public function setTotalPages(int $totalPages): Pagination
    {
        $this->_totalPages = $totalPages;
        return $this;
    }

    /**
     * @return int
     */
    public function getNextPage(): int
    {
        return $this->_nextPage;
    }

    /**
     * @param int $nextPage
     * @return Pagination
     */
    public function setNextPage(int $nextPage): Pagination
    {
        $this->_nextPage = $nextPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPreviousPage(): int
    {
        return $this->_previousPage;
    }

    /**
     * @param int $previousPage
     * @return Pagination
     */
    public function setPreviousPage(int $previousPage): Pagination
    {
        $this->_previousPage = $previousPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getFirstPage(): int
    {
        return $this->_firstPage;
    }

    /**
     * @param int $firstPage
     * @return Pagination
     */
    public function setFirstPage(int $firstPage): Pagination
    {
        $this->_firstPage = $firstPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->_lastPage;
    }

    /**
     * @param int $lastPage
     * @return Pagination
     */
    public function setLastPage(int $lastPage): Pagination
    {
        $this->_lastPage = $lastPage;
        return $this;
    }

    private function __calculateResults() {
        $query = clone $this->getQuery();
        $countExpression = "COUNT(".str_replace(".*",".id",$query->getQueryPart("select")[0]).") AS cnt";
        $query->select($countExpression);
        $count = $query->execute()->fetch(\PDO::FETCH_OBJ)->cnt;

        $this->setTotalRecords($count);

        $pages = $count / $this->getPerPage();
        $pages = ($pages * $this->getPerPage() > $count) ? $pages+1 : $pages;
        $this->setTotalPages($pages);
        $this->setNextPage($this->getPage() < $pages ? $this->getPage() + 1 : $pages);
        $this->setPreviousPage($this->getPage() > 1 ? $this->getPage() - 1 : 1);
        $this->setFirstPage(1);
        $this->setLastPage($pages);

        $this->getQuery()->setFirstResult($this->getPage() * $this->getPerPage() - $this->getPerPage());
        $this->getQuery()->setMaxResults($this->getPerPage());

        $this->__calculatedResults = true;

        return $this->getQuery();
    }

}