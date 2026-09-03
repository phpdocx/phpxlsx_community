# phpxlsx Community Edition

[phpxlsx](https://www.phpxlsx.com) is a PHP library designed to dynamically generate spreadsheets in Excel format (SpreadsheetML)

**phpxlsx Community Edition** is a free, reduced version of the full [phpxlsx](https://www.phpxlsx.com) library. It includes a limited subset of the features available in the commercial editions.

The commercial editions provide a much broader feature set, including support for templates, HTML content, charts, headers, footers, watermarks, conversion plugin, encryption, digital signatures, JavaScript API, JSON API, and improved performance, together with technical support. Visit the [phpxlsx site](https://www.phpxlsx.com) for the full feature list and available licenses.

## Requirements

- PHP >= 5.6
- `ext-dom`
- `ext-xml`
- `ext-exif`
- `ext-gd`
- `ext-mbstring`
- `ext-zip`

## Installation

### Install with Composer

```sh
composer require phpdocx/phpxlsx_community
```

### Download and install

Download the project files and include the bundled autoloader in your PHP script:

```php
<?php
require_once __DIR__ . '/Classes/Phpxlsx/Create/CreateXlsx.php';
```

This loads the library classes automatically so you can use the phpxlsx classes in your project.

## Examples

The examples folder contains self-contained samples for all the public methods.

## Changelog

See CHANGELOG.md for release notes.

## License

This project is distributed under the terms described in the LICENSE file.