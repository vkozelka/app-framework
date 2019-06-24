<?php

namespace App\System\Mvc;

use App\System\App;
use App\System\Mvc\Model\Entity;
use App\System\Mvc\Model\RecordNotSavedException;
use App\System\Pagination;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\ORM\Query\Expr;

class Model
{

    /**
     * @var string
     */
    protected $_tableName = null;

    /**
     * @var string
     */
    protected $_tableAlias = null;

    /**
     * @var string
     */
    protected $_primaryKey = "id";

    /**
     * @var string
     */
    protected $_entityClass = null;

    /**
     * @var array
     */
    protected $_searchFields = [];

    /**
     * @var \Doctrine\DBAL\Connection|null
     */
    protected $_connection;

    public function __construct()
    {
        $this->_connection = App::get()->getDatabase()->getConnection();
    }

    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function getTableAlias()
    {
        return $this->_tableAlias;
    }

    public function getPrimaryKey()
    {
        return $this->_primaryKey;
    }

    public function query()
    {
        return $this->_connection->createQueryBuilder();
    }

    public function findFirst(int $id)
    {
        $statement = $this->_connection->createQueryBuilder()
            ->select("*")
            ->from($this->_tableName)
            ->where($this->_primaryKey . " = ?")
            ->setParameter(0, intval($id))
            ->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->_entityClass);
        $item = $statement->fetch();
        /** @var $item Entity */
        if (method_exists($this, "afterFetch")) {
            $item = $this->afterFetch($item);
        }
        return $item;
    }

    public function findFirstBy(string $column, $id)
    {
        $statement = $this->_connection->createQueryBuilder()
            ->select("*")
            ->from($this->_tableName)
            ->where($column . " = ?")
            ->setParameter(0, $id)
            ->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->_entityClass);
        $item = $statement->fetch();
        /** @var $item Entity */
        if (method_exists($this, "afterFetch")) {
            $item = $this->afterFetch($item);
        }
        return $item;
    }

    public function findFirstByMultiple(array $conditions)
    {
        $query = $this->_connection->createQueryBuilder()
            ->select("*")
            ->from($this->_tableName);
        $i = 0;
        foreach ($conditions as $column => $value) {
            $query->andWhere($column . " = :v" . $i);
            $query->setParameter("v" . $i, $value);
            $i++;
        }
        $statement = $query->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->_entityClass);
        $item = $statement->fetch();
        /** @var $item Entity */
        if (method_exists($this, "afterFetch")) {
            $item = $this->afterFetch($item);
        }
        return $item;
    }

    public function findBy(string $column, $value)
    {
        $statement = $this->_connection->createQueryBuilder()
            ->select("*")
            ->from($this->_tableName)
            ->where($column . " = ?")
            ->setParameter(0, $value)
            ->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->_entityClass);
        $items = $statement->fetchAll();
        $result = [];
        foreach ($items as $item) {
            /** @var $item Entity */
            if (method_exists($this, "afterFetch")) {
                $item = $this->afterFetch($item);
            }
            $result[] = $item;
        }
        return $result;
    }

    public function findAll($query = null, $page = 1, $perPage = 25)
    {
        $query = $this->_connection->createQueryBuilder()
            ->select("*")
            ->from($this->_tableName);
        if ($query && $this->hasSearchFields()) {
            $conditions = [];
            $expr = $this->_connection->getExpressionBuilder();
            foreach ($this->getSearchFields() as $field) {
                $conditions[] = $expr->like($field, "%".$query."%");
            }
            $query->andWhere($expr->orX($conditions));
        }

        $pagination = new Pagination($query, $page, $perPage);

        $statement = $pagination->getPaginatedQuery()->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->_entityClass);
        $items = $statement->fetchAll();
        $result = [];
        foreach ($items as $item) {
            /** @var $item Entity */
            if (method_exists($this, "afterFetch")) {
                $item = $this->afterFetch($item);
            }
            $result[] = $item;
        }
        return $result;
    }

    public function save(Entity $entity)
    {
        $data = $entity->getData();
        if (is_null($data[$this->_primaryKey])) {
            unset($data[$this->_primaryKey]);
            if (property_exists($entity, "created_at")) {
                if (!isset($data["created_at"]) || empty($data["created_at"])) {
                    $data["created_at"] = date("Y-m-d H:i:s");
                }
            }
            $data = $this->__checkTimestamps($data);
            return $this->_connection->insert($this->_tableName, $data);
        } else {
            // update
            if (property_exists($entity, "updated_at")) {
                if (!isset($data["updated_at"]) || empty($data["updated_at"])) {
                    $data["updated_at"] = date("Y-m-d H:i:s");
                }
            }
            $data = $this->__checkTimestamps($data);
            return $this->_connection->update($this->_tableName, $data, [$this->_primaryKey => $data[$this->_primaryKey]]);
        }
        throw new RecordNotSavedException();
    }

    public function delete(Entity $entity)
    {
        return $this->_connection->delete($this->_tableName, [$this->_primaryKey => $entity->{$this->_primaryKey}]);
    }

    public function getSearchFields() {
        return $this->_searchFields;
    }

    public function hasSearchFields() {
        return count($this->getSearchFields());
    }

    private function __checkTimestamps($data) {
        $dateFields = ["created_at","updated_at","deleted_at"];
        foreach ($data as $field => $value) {
            if (empty($value)) {
                unset($data[$field]);
            } elseif (in_array($field, $dateFields)) {
                if ($data[$field] == "0000-00-00 00:00:00" || $data[$field] == "null") {
                    unset($data[$field]);
                }
            }
        }
        return $data;
    }

}