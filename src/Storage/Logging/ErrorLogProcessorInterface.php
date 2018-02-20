<?php

namespace Lexide\Reposition\Storage\Logging;

interface ErrorLogProcessorInterface 
{

    /**
     * @param $query
     * @param array $errorInfo
     */
    public function recordError($query, array $errorInfo);

} 
