<?php
require_once 'init.php';
require_once('getWinner.php');

$lots = getLots($conn);

$categoriesFromDB = getCategories($conn);

$pageContent = include_template('main.php', [
    'categories' => $categoriesFromDB,
    'lots' => $lots,
]);


$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Главная',
    'userName' => $userName,
    'isAuth' => $isAuth,
    'categories' => $categoriesFromDB,
    'isPromo' => true
]);

print $pageLayout;
