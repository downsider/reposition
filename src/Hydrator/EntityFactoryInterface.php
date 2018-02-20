<?php

namespace Lexide\Reposition\Hydrator;

/**
 * EntityFactoryInterface
 */
interface EntityFactoryInterface
{

    /**
     * @param string $class
     * @param array $data
     * @return object
     */
    public function create($class, array $data = []);

}
