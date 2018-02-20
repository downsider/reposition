<?php
namespace Lexide\Reposition\QueryInterpreter;

use Lexide\Reposition\Exception\QueryException;
use Lexide\Reposition\Normaliser\NormaliserInterface;
use Lexide\Reposition\Query\DeleteQuery;
use Lexide\Reposition\Query\Query;
use Lexide\Reposition\Query\FindQuery;
use Lexide\Reposition\Query\InsertQuery;
use Lexide\Reposition\Query\UpdateQuery;

/**
 *
 */
class MongoQueryInterpreter implements QueryInterpreterInterface
{

    /**
     * @var NormaliserInterface
     */
    protected $normaliser;

    /**
     * {@inheritDoc}
     * @throws \Lexide\Reposition\Exception\QueryException
     */
    public function interpret(Query $query)
    {
        switch ($query->getAction()) {
            case Query::ACTION_FIND:
                /** @var FindQuery $query */
                return $this->compileFindQuery($query);
            case Query::ACTION_INSERT:
                /** @var InsertQuery $query */
                return $this->compileInsertQuery($query);
            case Query::ACTION_UPDATE:
                /** @var UpdateQuery $query */
                return $this->compileUpdateQuery($query);
            case Query::ACTION_DELETE:
                /** @var DeleteQuery $query */
                return $this->compileDeleteQuery($query);
            default:
                throw new QueryException("Invalid query action: {$query->getAction()}");
        }
    }

    /**
     * @param FindQuery $query
     * @return CompiledQuery
     */
    protected function compileFindQuery(FindQuery $query)
    {
        $calls = [];
        $limit = $query->getLimit();
        if (!empty($limit)) {
            $calls[] = ["limit", [$limit]];
        }
        $sort = $query->getSort();
        if (!empty($sort)) {
            foreach ($sort as $field => $direction) {
                $sort[$field] = ($direction == Query::SORT_ASCENDING)? 1: -1;
            }
            $calls[] = ["sort", [$this->normalise($sort, ["filter" => "keys"])]];
        }

        return new CompiledQuery(
            $query->getTable(),
            "find",
            [
                $this->normalise($query->getFilters())
            ],
            $calls
        );
    }

    protected function compileInsertQuery(InsertQuery $query)
    {
        return new CompiledQuery(
            $query->getTable(),
            "insert",
            [
                $this->normalise($query->getValues())
            ]
        );
    }

    protected function compileUpdateQuery(UpdateQuery $query)
    {
        return new CompiledQuery(
            $query->getTable(),
            "update",
            [
                $this->normalise($query->getFilters()),
                ["\$set" => $this->normalise($query->getValues())],
                ["multiple" => true]
            ]
        );
    }

    protected function compileDeleteQuery(DeleteQuery $query)
    {
        return new CompiledQuery(
            $query->getTable(),
            "remove",
            [
                $this->normalise($query->getFilters())
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setNormaliser(NormaliserInterface $normaliser)
    {
        $this->normaliser = $normaliser;
    }

    /**
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function normalise(array $data, array $options = [])
    {
        if ($this->normaliser instanceof NormaliserInterface) {
            return $this->normaliser->normalise($data, $options);
        }
        return $data;
    }

} 
