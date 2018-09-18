# Base [![Latest Stable Version](https://poser.pugx.org/PandCar/Base/v/stable.svg)](https://packagist.org/packages/pandcar/base) [![Total Downloads](https://poser.pugx.org/PandCar/Base/downloads)](https://packagist.org/packages/pandcar/base) ![compatible](https://img.shields.io/badge/php-%3E=5.4-green.svg)

## Установка

Для установки Base выполните команду:

```sh
composer require pandcar/base
```

## Cтарт

```php
require 'vendor/autoload.php';

$data = [
   'host' => 'localhost',
   'user' => 'root',
   'pass' => '',
   'base' => 'site-db',
   'charset' => 'utf8'
];

if (! Base::connect('mysql', $data))
{
   die('Нет конекта.');
}
```