<?php

namespace Hail\Path;

/**
 * Class Path
 *
 * @package Hail\Path
 */
class BasePath
{
    /**
     * @var string
     */
    private $base;

    /**
     * @var array
     */
    private $struct;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(string $base, bool $autoCreate = false)
    {
        if (false === ($base = \realpath($base)) && (!$autoCreate || !Helper::create($base))) {
            throw new \InvalidArgumentException("The base path `$base` not exists");
        }

        $this->base = $base;
    }

    public function base(): string
    {
        return $this->base;
    }

    public function absolute(string ...$paths): string
    {
        if ($paths === []) {
            return $this->base;
        }

        $path = Helper::join($paths);

        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $return = Helper::normalize($this->base, $path);

        if (\strpos($return . DIRECTORY_SEPARATOR, $this->base . DIRECTORY_SEPARATOR) !== 0) {
            throw new \InvalidArgumentException('Can not higher than base path.');
        }

        return $this->cache[$path] = $return;
    }

    public function relative(string ...$paths): string
    {
        if ($this->struct === null) {
            $this->struct = Helper::split($this->base);
        }

        return Helper::relativeInternal($paths, $this->struct);
    }

    public function create(string ...$path): bool
    {
        $dir = $this->absolute(...$path);

        return Helper::create($dir);
    }
}
