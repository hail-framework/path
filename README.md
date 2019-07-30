# Path

# Example

```php
use Hail\Path\{Paths, Path};

__DIR__  . DIRECTORY_SEPARATOR .  'storage' === Path::normalize(__DIR__, 'storage'); // true

$paths = new Paths([
    'root' => __DIR__,
    'storage' => __DIR__ .  '/storage',
]);

$paths->root instanceof Path; // true
$paths->root->base() === $paths->root(); // true
$paths->root->absolute('a', 'b') === $paths->root('a', 'b'); // true

$paths->storage instanceof Path; // true
$paths->storage->base() === $paths->root('storage'); // true
$paths->root->relative($paths->storage()) === 'storage'; // true


```
