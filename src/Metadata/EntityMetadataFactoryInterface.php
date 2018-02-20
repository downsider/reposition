<?php

namespace Lexide\Reposition\Metadata;

/**
 * Interface for creating new, empty or partially decorated metadata for a given entity
 *
 * @package Lexide\Reposition
 */
interface EntityMetadataFactoryInterface
{

    /**
     * @param string|object $reference - class name or object instanced
     *
     * @return EntityMetadata
     */
    public function createMetadata($reference);

    /**
     * Create a new metadata object with no configuration
     *
     * @return EntityMetadata
     */
    public function createEmptyMetadata();

} 
