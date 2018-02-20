<?php

namespace Lexide\Reposition\QueryInterpreter;

use Lexide\Reposition\Normaliser\NormaliserInterface;
use Lexide\Reposition\QueryBuilder\TokenSequencerInterface;
use Lexide\Reposition\Metadata\EntityMetadataProviderInterface;

/**
 *
 */
interface QueryInterpreterInterface 
{

    /**
     * @param TokenSequencerInterface $query
     * @return CompiledQuery
     */
    public function interpret(TokenSequencerInterface $query);

    /**
     * @param NormaliserInterface $normaliser
     */
    public function setNormaliser(NormaliserInterface $normaliser);

    /**
     * @param EntityMetadataProviderInterface $provider
     */
    public function setEntityMetadataProvider(EntityMetadataProviderInterface $provider);

} 
