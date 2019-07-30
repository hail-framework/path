<?php

namespace Hail\Path;

/**
 * Class Paths
 *
 * @package Hail\Path
 */
class Paths
{
    private $base = [];

    public function __construct(array $bases = [])
    {
        foreach ($bases as $k => $v) {
            $this->$k = new Path($v);
            $this->base[$k] = true;
        }
    }

    public function absolute(string $root, string ...$paths): string
    {
        if ($root[0] === '@') {
            $root = substr($root, 1);
        }

        if (!isset($this->base[$root])) {
            throw new \RuntimeException("Base path not defined '$root'");
        }

        return $this->$root->absolute(...$paths);
    }

    /**
     * @param string   $name
     * @param string[] $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        if (!isset($this->base[$name])) {
            throw new \RuntimeException("Base path not defined '$name'");
        }

        return $this->$name->absolute(...$arguments);
    }
}
