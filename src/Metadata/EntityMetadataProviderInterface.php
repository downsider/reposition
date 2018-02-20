<?php

namespace Lexide\Reposition\Metadata;

/**
 * Interface for providers of complete, decorated metadata for a given entity

 * @package Lexide\Reposition
 */
interface EntityMetadataProviderInterface 
{

    /**
     * @param $entity
     *
     * @return EntityMetadata
     */
    public function getEntityMetadata($entity);

    /**
     * Provider the metadata for the intermediary collection of a Many to many relationship
     *
     * @param $collection
     *
     * @return EntityMetadata
     */
    public function getEntityMetadataForIntermediary($collection);

} 
