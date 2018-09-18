# Base [![Latest Stable Version](https://poser.pugx.org/PandCar/Base/v/stable.svg)](https://packagist.org/packages/pandcar/base) [![Total Downloads](https://poser.pugx.org/PandCar/Base/downloads)](https://packagist.org/packages/pandcar/base) ![compatible](https://img.shields.io/badge/php-%3E=5.4-green.svg)

## Установка

Для установки Base выполните команду:

```sh
composer require pandcar/base
```

## Cтарт

```php
require 'vendor/autoload.php';

// Включение отладки
Base::$debug = 2;

$data = [
   'host' => 'localhost',
   'user' => 'root',
   'pass' => '',
   'base' => 'site-db',
   'charset' => 'utf8'
];

$pdo = Base::connect('mysql', $data) or die('Нет подключения.');
```

## Select

### Примеры выбора строки по её id

```php
// №1 Самый короткий способ 
$row = Base::get('table', 1);

// №2 Строчный
$row = Base::get('table', '`id` = 1');

// №3 Строчный безопасный
$row = Base::get('table', '`id` = :id', [
   'id' => 1
]);

// №4 Массивом
$row = Base::get('table', [
   'id' => 1
]);

// №5 Запросом
$row = Base::query('SELECT * FROM `table` WHERE `id` = :id', [
   'id' => 1
]);
```