<?php

namespace Hail\Path;

/**
 * Class Paths
 *
 * @package Hail\Path
 */
class Paths
{
    private $bases = [];

    public function __construct(array $bases = [], bool $autoCreate = false)
    {
        foreach ($bases as $k => $v) {
            $this->$k = new Path($v, $autoCreate);
            $this->bases[$k] = true;
        }
    }

    /**
     * @param string   $name
     * @param string[] $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        if (!isset($this->bases[$name])) {
            throw new \RuntimeException("Base path not defined '$name'");
        }

        return $this->$name->absolute(...$arguments);
    }

    public function bases(): array
    {
        return $this->bases;
    }
}
