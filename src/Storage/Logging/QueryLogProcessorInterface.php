<?php

namespace Lexide\Reposition\Storage\Logging;

use Lexide\Reposition\QueryInterpreter\CompiledQuery;

interface QueryLogProcessorInterface 
{

    /**
     * @param CompiledQuery $query
     */
    public function recordQueryStart(CompiledQuery $query);

    public function recordQueryEnd();

} 
