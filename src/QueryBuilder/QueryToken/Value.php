<?php

namespace Lexide\Reposition\QueryBuilder\QueryToken;

use Lexide\Reposition\Exception\QueryException;

class Value extends Token
{

    const TYPE_STRING = "string";
    const TYPE_INT = "int";
    const TYPE_INTEGER = "integer";
    const TYPE_FLOAT = "float";
    const TYPE_BOOL = "bool";
    const TYPE_BOOLEAN = "boolean";
    const TYPE_NULL = "null";
    const TYPE_ARRAY = "array";
    const TYPE_DATETIME = "datetime";

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string $type
     * @param mixed $value
     */
    public function __construct($type, $value)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

}
