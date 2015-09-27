<?php

namespace Silktide\Reposition\Repository;

use Silktide\Reposition\QueryBuilder\TokenSequencerInterface;
use Silktide\Reposition\QueryBuilder\QueryBuilderInterface;
use Silktide\Reposition\Storage\StorageInterface;
use Silktide\Reposition\Metadata\EntityMetadata;

/**
 *
 */
abstract class AbstractRepository implements RepositoryInterface
{

    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var QueryBuilderInterface
     */
    protected $queryBuilder;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @param EntityMetadata $entityMetadata
     * @param QueryBuilderInterface $queryBuilder
     * @param StorageInterface $storage
     */
    public function __construct(EntityMetadata $entityMetadata, QueryBuilderInterface $queryBuilder, StorageInterface $storage)
    {
        $this->entityMetadata = $entityMetadata;
        $this->queryBuilder = $queryBuilder;
        $this->storage = $storage;
        $this->configureMetadata();
    }

    /**
     * Configure the metadata for the entity this repository interacts with
     *
     * Override this method to set additional fields or define relationships with other entities
     *
     */
    protected function configureMetadata()
    {
        $this->entityMetadata->setTable($this->tableName);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityName()
    {
        return $this->entityMetadata->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        $query = $this->queryBuilder->find($this->entityMetadata->getTable())
            ->where()
            ->ref(QueryBuilderInterface::PRIMARY_KEY)
            ->op("=")
            ->val($id);
        return $this->doQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function filter(array $filters, array $sort = [], $limit = 0, array $options = [])
    {
        $query = $this->queryBuilder->find($this->entityMetadata->getTable());

        $this->createWhereFromFilters($query, $filters);

        if (!empty($sort)) {
            $query->sort($sort);
        }

        if (!empty($limit)) {
            $query->limit($limit);
        }

        return $this->doQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function save($entity, array $options = [])
    {
        $query = $this->queryBuilder->save($this->entityMetadata->getTable())->entity($entity);
        return $this->doQuery($query, false);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $query = $this->queryBuilder->delete($this->tableName)
            ->where()
            ->ref(QueryBuilderInterface::PRIMARY_KEY)
            ->op("=")
            ->val($id);
        return $this->doQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function count(array $conditions = [], array $groupBy = [])
    {
        $query = $this->queryBuilder->find($this->entityMetadata->getTable())->aggregate("count", "*");

        $this->createWhereFromFilters($query, $conditions);

        if (!empty($groupBy)) {
            $query->group($groupBy);
        }

        return $this->doQuery($query, false);
    }

    /**
     * @param TokenSequencerInterface $query
     * @param bool $createEntity
     *
     * @return object|array
     */
    protected function doQuery(TokenSequencerInterface $query, $createEntity = true)
    {
        return $this->storage->query($query, $createEntity? $this->entityMetadata->getEntity(): "");
    }

    protected function createWhereFromFilters(TokenSequencerInterface $query, array $filters, $startWithWhere = true)
    {
        if (empty($filters)) {
            return;
        }

        if ($startWithWhere) {
            $query->where();
        }

        // we need to add "andL" to all but the last field, so
        // get the values for the last field and remove it from the array
        end($filters);
        $lastField = key($filters);
        $lastValue = array_pop($filters);
        reset($filters);

        // create filters
        for ($filters as $field => $value) {
            $query->ref($field)->op("=")->val($value)->andL();
        }
        // filter last field
        $query->ref($lastField)->op("=")->val($lastValue);
    }

} 