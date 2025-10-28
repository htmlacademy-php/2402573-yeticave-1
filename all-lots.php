<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoryId = $_GET['id'] ?? null;

$categoriesFromDB = getCategories($conn);

if (!$categoryId) {
    http_response_code(404);
    die('Категория не указана');
}

$category = getCategoriesById($conn, (int)$categoryId);
if (!$category) {
    http_response_code(404);
    die('Категория не найдена');
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$lots = getLotsByCategory($conn, (int)$categoryId, $limit, $offset);

$totalLots = getLotsCountByCategory($conn, (int)$categoryId);
$pagesCount = ceil($totalLots / $limit);

$pageContent = include_template('all-lots.php', [
    'category' => $category,
    'categories' => $categoriesFromDB,
    'lots' => $lots,
    'pagesCount' => $pagesCount,
    'currentPage' => $page
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Все лоты в категории ' . $category['title'],
    'userName' => $_SESSION['user']['name'] ?? ''
]);

print $pageLayout;
