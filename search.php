<?php

require_once 'init.php';

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
    $pagesCount = $pagination['pagesCount'] ?? 1;
}

$pageContent = include_template('all-lots.php', [
    'lots' => $lots,
    'categories' => $categoriesFromDB,
    'currentPage' => $currentPage,
    'pagesCount' => $totalPages ?? 1,
    'search' => $search
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Результаты поиска',
    'userName' => $userName,
    'isAuth' => $isAuth,
    'categories' => $categoriesFromDB,
    'simpleCategoriesMenu' => true
]);

print $pageLayout;
