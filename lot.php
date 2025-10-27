<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$id  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$sql = 'SELECT l.id, l.title AS lot_title, l.starting_price, l.description, l.image, c.title AS category_title, l.end_date, c.symbol_code
FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.id = ?;';

$stmt = db_get_prepare_stmt($conn, $sql, [$id]);
mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);
$lot = mysqli_fetch_assoc($res);

if (!$lot) {
    http_response_code(404);
    exit('Page not found');
}

$categoriesFromDB = getCategories($conn);

$bidsHistory = getBidsByLot($conn, $lot['id']);
$currentPrice = getLotCurrentPrice($conn, $lot['id']);

$singleLot = include_template('lot.php', [
    'lot' => $lot,
    'bidsHistory' => $bidsHistory,
    'currentPrice' => $currentPrice
]);

$lotContent = include_template('layout.php', [
    'pageContent' => $singleLot,
    'title' => $lotId['lot_title'],
    'categories' => $categoriesFromDB
]);

print $lotContent;
