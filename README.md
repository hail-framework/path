# Path

# Example

```php
use Hail\Path\{Path, BasePath};

$paths = new Path([
    'root' => __DIR__,
    'storage' => __DIR__ .  '/storage',
]);

$paths->root instanceof BasePath; // true
$paths->root->base() === $paths->root(); // true
$paths->root->absolute('a', 'b') === $paths->root('a', 'b'); // true

$paths->storage instanceof BasePath; // true
$paths->storage->base() === $paths->root('storage'); // true
$paths->root->relative($paths->storage()) === 'storage'; // true
```
