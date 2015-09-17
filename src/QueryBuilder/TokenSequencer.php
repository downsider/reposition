<?php

namespace Silktide\Reposition\QueryBuilder;

use Silktide\Reposition\QueryBuilder\QueryToken\TokenFactory;
use Silktide\Reposition\QueryBuilder\QueryToken\Token;

class TokenSequencer 
{
    const TYPE_EXPRESSION = "expression";
    const TYPE_FIND = "find";
    const TYPE_SAVE = "save";
    const TYPE_UPDATE = "update";
    const TYPE_DELETE = "delete";

    const SORT_ASC = 1;
    const SORT_DESC = -1;

    protected $tokenFactory;

    protected $type;

    protected $collectionName;

    protected $querySequence = [];

    protected $filtering = false;

    protected $currentSection = "initial";

    public function __construct(TokenFactory $tokenFactory, $type = self::TYPE_EXPRESSION, $collectionName = "")
    {
        $this->tokenFactory = $tokenFactory;
        $this->setType($type);
        $this->collectionName = $collectionName;
    }

    protected function setType($type)
    {
        switch ($type) {
            case self::TYPE_EXPRESSION:
            case self::TYPE_FIND:
            case self::TYPE_SAVE:
            case self::TYPE_UPDATE:
            case self::TYPE_DELETE:
                $this->type = $type;
                break;
            default:
                throw new \InvalidArgumentException("Cannot create a QueryBuilder with the type '$type'");
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function isQuery()
    {
        return ($this->type != self::TYPE_EXPRESSION);
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    public function getSequence()
    {
        return $this->querySequence;
    }

    protected function addToSequence(Token $token)
    {
        $this->querySequence[] = $token;
    }

    protected function addNewToSequence($type, $value = null, $alias = null)
    {
        $this->querySequence[] = $this->tokenFactory->create($type, $value, $alias);
    }

    protected function mergeSequence(array $sequence)
    {
        $this->querySequence = array_merge($this->querySequence, $sequence);
    }

    protected function addMixedContentToSequence($content, $defaultType)
    {
        if ($content instanceof TokenSequencer) {
            $this->closure($content);
        } elseif ($content instanceof Token) {
            $this->addToSequence($content);
        } else {
            $this->addNewToSequence($defaultType, $content);
        }
    }

    ////////// QUERY SECTION METHODS //////////

    public function aggregate($type)
    {
        $values = func_get_args();
        // already have type so remove that from the array
        array_shift($values);

        $type = strtolower($type);

        switch ($type) {
            case "count":
            case "sum":
            case "maximum":
            case "mininum":
            case "average":
                $this->func($type, $values);
                break;
            default:
                throw new \InvalidArgumentException("The aggregate function '$type' is invalid");
        }
        return $this;
    }

    public function where()
    {
        $this->addNewToSequence("where");
        return $this;
    }

    public function order(array $by)
    {
        $this->addNewToSequence("order");
        foreach ($by as $ref => $direction) {
            $this->addMixedContentToSequence($ref, "field");
            $this->addNewToSequence("sort direction", ($direction == self::SORT_DESC)? $direction: self::SORT_ASC );
        }
        return $this;
    }

    public function limit($limit, $offset = null)
    {
        $limit = (int) $limit;
        if ($limit <= 0) {
            throw new \InvalidArgumentException("Cannot have a limit less than 1");
        }
        $this->addNewToSequence("limit");
        $this->addNewToSequence("integer", $limit);
        if (!is_null($offset)) {
            $offset = (int) $offset;
            if ($limit < 0) {
                throw new \InvalidArgumentException("Cannot have an offset less than 0");
            }
            $this->addNewToSequence("offset");
            $this->addNewToSequence("integer", $offset);
        }
        return $this;
    }

    public function group(array $by)
    {
        $this->addNewToSequence("group");
        foreach ($by as $ref) {
            $this->addMixedContentToSequence($ref, "field");
        }
        return $this;
    }

    ////////// LOGIC METHODS //////////
    // suffixed with 'L' because we can't use keywords as method names in PHP 5.5

    public function notL()
    {
        $this->addNewToSequence("not");
        return $this;
    }

    public function andL()
    {
        $this->addNewToSequence("and");
        return $this;
    }

    public function orL()
    {
        $this->addNewToSequence("or");
        return $this;
    }

    /**
     * wraps the following expression in parentheses to isolate it
     */
    public function closure($content = null)
    {
        $this->addNewToSequence("closure start");

        if (!empty($content)) {
            if ($content instanceof TokenSequencer) {
                $this->mergeSequence($content->getSequence());
            } elseif ($content instanceOf Token) {
                $this->addToSequence($content);
            } elseif (is_array($content)) {
                foreach ($content as $subcontent) {
                    $this->addMixedContentToSequence($subcontent, "value");
                }
            } else {
                $this->addNewToSequence("value", $content);
            }
        }

        $this->addNewToSequence("closure end");

        return $this;
    }

    ////////// EXPRESSION METHODS //////////

    public function ref($name, $alias = "", $type = "field")
    {
        $this->addNewToSequence($type, $name, $alias);
        return $this;
    }

    public function op($value)
    {
        $this->addNewToSequence("operator", $value);
        return $this;
    }

    public function val($value)
    {
        $this->addNewToSequence("value", $value);
        return $this;
    }

    public function func($name, array $args = [])
    {
        $this->addNewToSequence("function", $name);
        $this->closure($args);
        return $this;
    }

    public function keyword($keyword)
    {
        $this->addNewToSequence($keyword);
        return $this;
    }
} 