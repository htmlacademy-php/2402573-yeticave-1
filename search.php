<?php

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);

$search = trim($_GET['search'] ?? '');
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$lotsPerPage = 9;

$lots = [];
$totalPages = 0;


if ($search) {
    $totalLots = getLotsCountBySearch($conn, $search);
    $pagination = getPagination($totalLots, $currentPage, $lotsPerPage);

    $lots = getLotsBySearch($conn, $search, $pagination['limit'], $pagination['offset']);
    $totalPages = $pagination['pagesCount'];
}


$pageContent = include_template('all-lots.php', [
    'lots' => $lots,
    'categories' => $categoriesFromDB,
    'currentPage' => $currentPage,
    'totalPages' => $totalPages,
    'search' => $search
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Результаты поиска',
    'userName' => $_SESSION['user']['name'] ?? '',
    'categories' => $categoriesFromDB,
    'simpleCategoriesMenu' => true
]);

print $pageLayout;

