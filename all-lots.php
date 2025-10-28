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
    'lots' => $lots,
    'categories' => $categoriesFromDB,
    'currentPage' => $currentPage,
    'pagesCount' => $totalPages ?? $pagesCount ?? 1,
    'search' => $search ?? null,
    'currentCategoryId' => $category['id'] ?? null,
    'category' => $category ?? null
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Результаты поиска',
    'userName' => $_SESSION['user']['name'] ?? '',
    'categories' => $categoriesFromDB,
    'search' => $search,

]);
print $pageLayout;
