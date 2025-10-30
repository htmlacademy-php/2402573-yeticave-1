<?php

session_start();

$isAuth = isset($_SESSION['user']);
$userName = $_SESSION['user']['name'] ?? '';

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');
require_once('getWinner.php');

$conn = connectDB($db['db']);

$lots = getLots($conn);

$categoriesFromDB = getCategories($conn);

$pageContent = include_template('main.php', [
    'categories' => $categoriesFromDB,
    'lots' => $lots,
]);


$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Главная',
    'userName' => 'Анастасия',
    'categories' => $categoriesFromDB,
    'isPromo' => true
]);

print $pageLayout;
