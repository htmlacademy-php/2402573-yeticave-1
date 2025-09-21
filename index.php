<?php
require_once('./helpers.php');

$conn = mysqli_connect('localhost', 'wsluser', '3D4e85t1', 'yeticave');

if (!$conn) {
     print("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');

$lotsSql = 'SELECT l.title, l.starting_price, l.image, l.end_date, c.title AS category FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.end_date > NOW()
ORDER BY l.created_at DESC;';
$result = mysqli_query($conn, $lotsSql);

if (!$result) {
	$error = mysqli_error($conn);
	print("Ошибка MySQL: " . $error);
}

$newLots = mysqli_fetch_all($result, MYSQLI_ASSOC);

$categoriesSql = 'SELECT * FROM categories;';
$res = mysqli_query($conn, $categoriesSql);
$categoriesFromDB = mysqli_fetch_all($res, MYSQLI_ASSOC);


$categories = ['Доски и лыжи', 'Крепления', 'Ботинки', 'Одежда', 'Инструменты', 'Разное'];

$lots = [
    [
        'name'      => '2014 Rossignol District Snowboard',
        'category'  => 'Доски и лыжи',
        'price'     => 10999,
        'image_url' => 'img/lot-1.jpg',
        'endDate' => '2025-09-10'
    ],
    [
        'name'      => 'DC Ply Mens 2016/2017 Snowboard',
        'category'  => 'Доски и лыжи',
        'price'     => 159999,
        'image_url' => 'img/lot-2.jpg',
        'endDate' => '2025-09-13'
    ],
    [
        'name'      => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'category'  => 'Крепления',
        'price'     => 8000,
        'image_url' => 'img/lot-3.jpg',
        'endDate' => '2025-09-12'
    ],
    [
        'name'      => 'Ботинки для сноуборда DC Mutiny Charocal',
        'category'  => 'Ботинки',
        'price'     => 10999,
        'image_url' => 'img/lot-4.jpg',
        'endDate' => '2025-09-15'
    ],
    [
        'name'      => 'Куртка для сноуборда DC Mutiny Charocal',
        'category'  => 'Одежда',
        'price'     => 7500,
        'image_url' => 'img/lot-5.jpg',
        'endDate' => '2025-09-16'
    ],
    [
        'name'      => 'Маска Oakley Canopy',
        'category'  => 'Разное',
        'price'     => 5400,
        'image_url' => 'img/lot-6.jpg',
        'endDate' => '2025-09-14'
    ],
];


$pageContent = include_template('main.php', [
    'categories' => $categoriesFromDB,
    'lots' => $newLots,
]);


$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Главная',
    'userName' => 'Анастасия',
    'categories' => $categoriesFromDB,
]);

print $pageLayout;
