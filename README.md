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

```php
// Самый короткий способ, выбор строки по её id
$row = Base::get('table', 1);

// Массивом
$row = Base::get('table', [
   'id' => 1,
   'login' => $login
]);

// Более сложная логика
$row = Base::get('table', 'id = 1 or login = :login', [
   'login' => $login
]);

// Запросом с указанием типа
$row = Base::query('SELECT * FROM `table` WHERE `id` = :id AND `login` = :login', [
   'id/int' => 1,
   'login' => $login
]);
```