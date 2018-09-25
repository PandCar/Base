# Base [![Latest Stable Version](https://poser.pugx.org/PandCar/Base/v/stable.svg)](https://packagist.org/packages/pandcar/base) [![Total Downloads](https://poser.pugx.org/PandCar/Base/downloads)](https://packagist.org/packages/pandcar/base) ![compatible](https://img.shields.io/badge/php-%3E=5.5-green.svg)

## Установка

Для установки Base выполните команду:

```sh
composer require pandcar/base
```

## Cтарт

```php
require 'vendor/autoload.php';

// Включение отладки
// 0 - выключено, 1 - только ошибки, 2 - полный отчёт
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

## SELECT

### Выбор одной строки

```php
// Самый короткий способ, по её id
$row = Base::get('table', 1);

// Массивом, равенство через and
$row = Base::get('table', [
   'id' => 1,
   'login' => $login
]);

// Более сложная логика
$row = Base::get('table', 'id = 1 or login = :login', [
   'login' => $login
]);

// Запросом с указанием типа
$row = Base::query('SELECT * FROM `table` WHERE `id` = :id AND `login` = :login LIMIT 1', [
   'id/int' => 1,
   'login' => $login
]);
```

### Другие результаты

```php
// Получение всех строк в виде массива
$array = Base::query(
    'SELECT * FROM `table` WHERE `id` > :id', [
       'id' => 5
    ],
    'arr'
);

// Получение всех строк в виде генератора (в случаях когда не нужно грузить все данные разом в память)
$generator = Base::query(
    'SELECT * FROM `table` WHERE `id` > :id', [
       'id' => 5
    ],
    'gen'
);

// Количество строк (короткий способ)
$count = Base::count('table', 'id > :id', [
   'id' => 5
]);

// Количество строк запросом
$count = Base::query('SELECT COUNT(`id`) FROM `table` WHERE `id` > :id', [
   'id' => 5,
]);
```

## UPDATE

```php
// Короткий способ, там где id = 5
$bool = Base::update('table', 5, [
    'email' => $new_email,
    'pass' => $new_pass
]);

// Другие условия
$bool = Base::update('table', [
    'login' => $login
], [
    'email' => $new_email,
    'pass' => $new_pass
]);

// Запросом
$bool = Base::query('UPDATE `table` SET `email` = :email, `pass` = :pass WHERE `login` = :login', [
    'email' => $new_email,
    'pass' => $new_pass,
    'login' => $login
]);
```

## INSERT

```php
// Короткий способ
$insert_id = Base::add('table', [
    'login' => $login,
    'pass' => $pass,
    'email' => $email
]);

// Запросом
$insert_id = Base::query('INSERT INTO `table` (`login`, `pass`, `email`) VALUES (:login, :pass, :email)', [
    'login' => $login,
    'pass' => $pass,
    'email' => $email
]);
```

## DELETE

```php
// Короткий способ, там где id = 5
$bool = Base::remove('table', 5);

// Тот же способ выбора как и у select
```