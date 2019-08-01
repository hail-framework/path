<?php

namespace Hail\Path;


class Helper
{
    public static function join(array $paths): string
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
        $path = self::join($paths);

        if ('~' === $path[0]) {
            $path = self::home() . substr($path, 1);
        }

        if (($absolute = \realpath($path)) === false) {
            [
                'schema' => $schema,
                'root' => $root,
                'path' => $path
            ] = self::split($path);

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
            return self::normalize($home);
        }

        if (($drive = \getenv('HOMEDRIVE')) && ($path = getenv('HOMEPATH'))) { // >= Windows 8
            return self::normalize($drive . $path);
        }

        throw new \RuntimeException('Your environment is not supported');
    }

    public static function root(string $path): string
    {
        [
            'schema' => $schema,
            'root' => $root,
        ] = self::split($path);

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

        ['root' => $root] = self::split(
            self::normalize($path)
        );

        return $root !== '';
    }

    public static function create(string $path, int $mode = 0777): bool
    {
        $path = self::normalize($path);

        if (\is_dir($path)) {
            return true;
        }

        if (!\mkdir($path, $mode, true) && !\is_dir($path)) {
            return false;
        }

        return true;
    }
}