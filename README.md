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
To automatically enable this for all web requests and entry points, you can use the PHP `auto_prepend_file` option.

```php
# site-wide-profiler.php
 
require __DIR__ . '/vendor/autoload.php';

use Ljfreelancer88\Gideon\Gideon;

new Gideon()->siteWide();
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
