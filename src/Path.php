<?php

namespace Hail\Path;

/**
 * Class Path
 *
 * @package Hail\Path
 */
class Path
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

    public function __construct(string $base)
    {
        if (false === ($base = \realpath($base))) {
            throw new \InvalidArgumentException("The base path `$base` not exists");
        }

        $this->base = $base;
    }

    public function absolute(string ...$paths): string
    {
        $path = self::join($paths);

        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $return = static::normalize($this->base, $path);

        if (\strpos($return . DIRECTORY_SEPARATOR, $this->base . DIRECTORY_SEPARATOR) !== 0) {
            throw new \InvalidArgumentException('Can not higher than base path.');
        }

        return $this->cache[$path] = $return;
    }

    public function relative(string ...$paths): string
    {
        if ($this->struct === null) {
            $this->struct = self::split($this->base);
        }

        [
            'schema' => $baseSchema,
            'root' => $baseRoot,
            'path' => $basePath
        ] = $this->struct;

        $path = static::normalize(...$paths);
        [
            'schema' => $schema,
            'root' => $root,
            'path' => $relativePath,
        ] = self::split($path);

        if ($schema !== $baseSchema || ($root !== '' && $baseRoot !== '' && $root !== $baseRoot)) {
            throw new \InvalidArgumentException("Paths have different roots ('{$schema}{$root}' and '{$baseSchema}{$baseRoot}').");
        }

        if ($basePath === '' || ('' === $root && '' !== $baseRoot)) {
            return $relativePath;
        }

        $parts = \explode(DIRECTORY_SEPARATOR, $relativePath);
        $baseParts = \explode(DIRECTORY_SEPARATOR, $basePath);

        $ddPrefix = '';
        $match = true;
        foreach ($baseParts as $i => $basePart) {
            if ($match && isset($parts[$i]) && $basePart === $parts[$i]) {
                unset($parts[$i]);
                continue;
            }
            $match = false;
            $ddPrefix .= '..' . DIRECTORY_SEPARATOR;
        }

        return rtrim($ddPrefix . implode(DIRECTORY_SEPARATOR, $parts), DIRECTORY_SEPARATOR);
    }

    public function create(string ...$path): bool
    {
        $dir = $this->absolute(...$path);

        return static::mkdir($dir);
    }

    protected static function join(array $paths): string
    {
        if ($paths === []) {
            return '';
        }

        if (isset($paths[1])) {
            return \implode(DIRECTORY_SEPARATOR, $paths);
        }

        return $paths[0];
    }

    public static function normalize(string ...$paths): string
    {
        $path = static::join($paths);

        if ('~' === $path[0]) {
            $path = static::home() . substr($path, 1);
        }

        if (($absolute = \realpath($path)) === false) {
            [
                'schema' => $schema,
                'root' => $root,
                'path' => $path
            ] = static::split($path);

            $parts = \explode('/', $path);

            $last = null;
            $absolutes = [];
            foreach ($parts as $part) {
                if ('.' === $part || '' === $part) {
                    continue;
                }

                if ('..' === $part && $absolutes !== [] && $last !== '..') {
                    \array_pop($absolutes);
                    continue;
                }

                $last = $part;
                $absolutes[] = $part;
            }

            $absolute = $schema . $root . implode(DIRECTORY_SEPARATOR, $absolutes);
        }

        return $absolute;
    }

    public static function home(): string
    {
        if ($home = \getenv('HOME')) { // UNIX
            return static::normalize($home);
        }

        if (($drive = \getenv('HOMEDRIVE')) && ($path = getenv('HOMEPATH'))) { // >= Windows 8
            return static::normalize($drive . $path);
        }

        throw new \RuntimeException('Your environment is not supported');
    }

    public static function root(string $path): string
    {
        [
            'schema' => $schema,
            'root' => $root,
        ] = static::split($path);

        return $schema . $root;
    }

    public static function split(string $path): array
    {
        if ($path === '') {
            return ['', ''];
        }

        $root = $schema = '';
        $parts = \explode('://', $path, 2);
        if (isset($parts[1])) {
            $schema = $parts[0] . '://';
            $path = $parts[1];
        } else {
            $path = $parts[0];
        }

        $path = \str_replace('\\', '/', $path);

        if ('/' === $path[0]) {
            $root = '/';
            $path = isset($path[1]) ? \substr($path, 1) : '';
        } elseif (
            isset($path[1]) && ':' === $path[1] &&
            ($ord = \ord($path[0] = \strtoupper($path[0]))) > 64 && $ord < 91 // A-Z => 65-90
        ) {
            $root = $path[0] . ':/';
            if (!isset($path[2])) {
                $path = '';
            } elseif ('/' === $path[2]) {
                $path = isset($path[3]) ? \substr($path, 3) : '';
            }
        }

        return [
            'schema' => $schema,
            'root' => $root,
            'path' => $path,
        ];
    }

    public static function isAbsolute(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        ['root' => $root] = static::split(
            static::normalize($path)
        );

        return $root !== '';
    }

    public static function mkdir(string $path, int $mode = 0777): bool
    {
        if (\is_dir($path)) {
            return true;
        }

        if (!\mkdir($path, $mode, true) && !\is_dir($path)) {
            return false;
        }

        return true;
    }
}
