# Gideon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ljfreelancer88/gideon.svg?style=flat-square)](https://packagist.org/packages/ljfreelancer88/gideon)
[![Total Downloads](https://img.shields.io/packagist/dt/ljfreelancer88/gideon.svg?style=flat-square)](https://packagist.org/packages/ljfreelancer88/gideon)
![GitHub Actions](https://github.com/ljfreelancer88/gideon/actions/workflows/main.yml/badge.svg)

Excimer-powered profiling tailored for PHP—gain real-time performance insights with minimal overhead. It is named after the biblical figure Gideon, who was known for his keen strategic insights and ability to assess and profile his enemies before battle.

## Installation

You can install the package via composer:

```bash
composer require ljfreelancer88/gideon
```

## Usage

### Site-wide profiling — only needs to run once per request (or per worker process)
To automatically enable this for all web requests and entry points, you can use the PHP `auto_prepend_file` option. 
This approach doesn't require modifying your existing codebase.

```php
require __DIR__ . '/vendor/autoload.php';

use Ljfreelancer88\Gideon\{
    FileWriter,
    Collect
};

$writer = new FileWriter('/tmp/excimer.log');

Collect::siteWideProfiling($writer);
```

### Per-request profiling — captures traces per HTTP request
```php
require __DIR__ . '/vendor/autoload.php';

use Ljfreelancer88\Gideon\{
    ExcimerWrapper,
    FileWriter,
    Collect
};

$profiler = new ExcimerWrapper();
$writer = new FileWriter('/tmp/excimer.log');
$collect = new Collect($profiler, $writer);
$collect->perRequestProfiling();
```

### Laravel Integration
```php
# Add this to your AppServiceProvider or a custom profiler service provider:
use Ljfreelancer88\Gideon\{
    Collect,
    ExcimerWrapper,
    FileWriter
};

public function register(): void
{
    $this->app->singleton(Collect::class, function () {
        $writer = new FileWriter(storage_path('logs/excimer.log'));
        $profiler = new ExcimerWrapper(60, 250); // 60s sampling, depth 250
        return new Collect($profiler, $writer);
    });
}

# Then in your App\Http\Kernel or a middleware:
public function boot(Collect $collect)
{
    $collect->perRequestProfiling();
}

# Or, for long-running queue workers:

// In your queue bootstrap or Laravel worker
use Ljfreelancer88\Gideon\FileWriter;
use Ljfreelancer88\Gideon\Collect;

Collect::siteWideProfiling(
    new FileWriter(storage_path('logs/excimer.log'))
);

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@minimaxweb.ca instead of using the issue tracker.

## Credits

-   [Jake Pucan](https://github.com/ljfreelancer88)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
