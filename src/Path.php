<?php

namespace Hail\Path;

/**
 * Class Path
 *
 * @package Hail\Path
 */
class Path
{
    public const DIRECTORY = 0;
    public const FILE = 1;

    private $base = [];

    public function __construct(array $bases = [])
    {
        foreach ($bases as $k => $v) {
            $this->base($k, $v);
        }
    }

    /**
     * @param string $key
     * @param string|null  $path
     *
     * @return string|null
     */
    public function base(string $key, string $path = null): ?string
    {
        if ($key[0] !== '@') {
            $key = '@' . $key;
        }

        if ($path === null) {
            return $this->base[$key] ?? null;
        }

        if (($absolute = \realpath($path)) === false) {
            throw new \InvalidArgumentException('Path not exists: ' . $path);
        }

        return $this->base[$key] = $absolute;
    }

    public function absolute(string $root, string ...$paths): string
    {
        if ($root[0] === '@') {
            $absoluteRoot = $this->base($root);
        } else {
            $root = \rtrim($root, '/\\');

            if (($absoluteRoot = \realpath($root)) === false) {
                throw new \InvalidArgumentException('ROOT path not exists: ' . $root);
            }
        }

        if ($paths === []) {
            return $absoluteRoot;
        }

        if (!isset($paths[1])) {
            $path = $paths[0];
        } else {
            $path = \implode('/', $paths);
        }

        $path = $absoluteRoot . '/' . \trim(
                \str_replace('\\', '/', $path),
                '/'
            );

        if (($absolutePath = \realpath($path)) === false) {
            $parts = \explode('/', $path);
            $absolutes = [];
            foreach ($parts as $part) {
                if ('.' === $part || '' === $part) {
                    continue;
                }

                if ('..' === $part) {
                    \array_pop($absolutes);
                } else {
                    $absolutes[] = $part;
                }
            }

            $absolutePath = implode(DIRECTORY_SEPARATOR, $absolutes);
            if ($absoluteRoot[0] === '/' && $absolutePath[0] !== '/') {
                $absolutePath = '/' . $absolutePath;
            }

            if (\strpos($absolutePath, $absoluteRoot) !== 0) {
                throw new \InvalidArgumentException('Path can not higher than ROOT.');
            }
        }

        return $absolutePath;
    }

    public function create(string $path, int $type = self::DIRECTORY, int $mode = 0777): bool
    {
        if ($type === self::FILE) {
            $path = \dirname($path);
        }

        if (\is_dir($path)) {
            return true;
        }

        if (!\mkdir($path, $mode, true) && !\is_dir($path)) {
            return false;
        }

        return true;
    }

    /**
     * @param string   $name
     * @param string[] $arguments
     *
     * @return string
     */
    public function __call(string $name, array $arguments): string
    {
        $name = '@' . $name;

        if (!isset($this->base[$name])) {
            throw new \RuntimeException("Base path not defined '$name'");
        }

        return $this->absolute($this->base[$name], ...$arguments);
    }
}
