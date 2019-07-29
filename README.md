# Path

# Example

```php
use Hail\Path\Path;

$path = new Path([
    '@root' => __DIR__,
    '@storage' => __DIR__ .  '/storage',
]);

__DIR__  . DIRECTORY_SEPARATOR .  'storage' === $path->absolute(__DIR__, 'storage'); // true
$path->root() === $path->absolute('@root'); // true
$path->root('storage') === $path->absolute('@root', 'storage'); // true
$path->storage() === $path->absolute('@storage'); // true
$path->storage('a', 'b') === $path->absolute('@storage', 'a', 'b'); // true

// create directory 
$bool = $path->create(
    $path->storage('a', 'b', 'c.txt'), Path::FILE
);
```
