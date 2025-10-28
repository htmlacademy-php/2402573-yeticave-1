<?php
session_start();

$isAuth = isset($_SESSION['user']);
$userName = $_SESSION['user']['name'] ?? '';

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');
require_once('getWinner.php');

$conn = connectDB($db['db']);

$lotsSql = 'SELECT l.id, l.title AS lot_title, l.starting_price, l.image, l.end_date, c.title AS category_title FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.end_date > NOW()
ORDER BY l.created_at DESC
LIMIT 9;';

$result = getQuery($conn, $lotsSql);
$lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
