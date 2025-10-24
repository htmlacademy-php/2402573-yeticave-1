<?php

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);

$search = trim($_GET['search'] ?? '');

$lots = [];
$lotsPerPage = 9;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

$offset = ($currentPage - 1) * $lotsPerPage;

if ($search) {
    $countLots = "
        SELECT COUNT(*) AS total_lots
        FROM lots
        WHERE MATCH(title, description) AGAINST(?)
            AND end_date > NOW()
    ";
    $stmtCount = mysqli_prepare($conn, $countLots);
    mysqli_stmt_bind_param($stmtCount, 's', $search);
    mysqli_stmt_execute($stmtCount);
    $resultCount = mysqli_stmt_get_result($stmtCount);
    $totalRow = mysqli_fetch_assoc($resultCount);
    $totalLots = $totalRow['total_lots'] ?? 0;
    $totalPages = ceil($totalLots / $lotsPerPage);

    $sql = "SELECT l.id,
                   l.title AS lot_title,
                   l.starting_price,
                   l.image,
                   c.title AS category_title,
                   l.created_at,
                   l.end_date
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE MATCH(l.title, l.description) AGAINST(? IN BOOLEAN MODE)
                AND l.end_date > NOW()
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?;";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $search, $lotsPerPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $totalLots = 0;
    $totalPages = 0;
}

$pageContent = include_template('search.php', [
    'lots' => $lots,
    'search' => $search,
    'totalPages' => $totalPages,
    'currentPage' => $currentPage
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Результаты поиска',
    'userName' => $_SESSION['user']['name'] ?? '',
    'categories' => $categoriesFromDB,
    'search' => $search
]);

print $pageLayout;
