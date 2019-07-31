<?php

namespace Hail\Path;

/**
 * Class Paths
 *
 * @package Hail\Path
 */
class Paths
{
    /**
     * @var array
     */
    private $bases;

    /**
     * @var bool
     */
    private $autoCreate;

    public function __construct(array $bases = [], bool $autoCreate = false)
    {
        $this->bases = $bases;
        $this->autoCreate = $autoCreate;
    }

    /**
     * @param string   $name
     * @param string[] $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        return $this->$name->absolute(...$arguments);
    }

    public function __get(string $name): Path
    {
        if (!isset($this->bases[$name])) {
            throw new \RuntimeException("Base path not defined '$name'");
        }

        return $this->$name = new Path($this->bases[$name], $this->autoCreate);
    }

    public function bases(): array
    {
        return $this->bases;
    }
}
