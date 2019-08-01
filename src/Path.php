<?php

namespace Hail\Path;

use Hail\Console\Command\Help;

/**
 * Class Paths
 *
 * @package Hail\Path
 */
class Path
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

    public function __get(string $name): BasePath
    {
        if (!isset($this->bases[$name])) {
            throw new \RuntimeException("Base path not defined '$name'");
        }

        return $this->$name = new BasePath($this->bases[$name], $this->autoCreate);
    }

    public function bases(): array
    {
        return $this->bases;
    }

    public function normalize(string ...$paths): string
    {
        return Helper::normalize(...$paths);
    }

    public static function relative(string $path, string $base): string
    {
        return Helper::relative($path, $base);
    }

    public function home(): string
    {
        return Helper::home();
    }

    public function root(string $path): string
    {
        return Helper::root($path);
    }

    public function split(string $path): array
    {
        return Helper::split($path);
    }

    public function isAbsolute(string $path): bool
    {
        return Helper::isAbsolute($path);
    }

    public function create(string $path, int $mode = 0777): bool
    {
        return Helper::create($path);
    }
}
