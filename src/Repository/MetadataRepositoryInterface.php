<?php

namespace Lexide\Reposition\Repository;

use Lexide\Reposition\Metadata\EntityMetadata;

interface MetadataRepositoryInterface 
{

    /**
     * @return EntityMetadata
     */
    public function getEntityMetadata();

} 
