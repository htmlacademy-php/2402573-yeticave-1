<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$lotId  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$lot = getLotById($conn, $lotId);

if (!$lot) {
    http_response_code(404);
    exit('Page not found');
}

$categoriesFromDB = getCategories($conn);

$bidsHistory = getBidsByLot($conn, $lot['id']);
$lastBid = $bidsHistory[0] ?? null;

$isLotExpired = isBidExpired($lot);

$currentPrice = getLotCurrentPrice($conn, $lot['id']);
$minBid = $currentPrice + $lot['bidding_step'];

$userId = (int)$_SESSION['user']['id'];

$isFormVisible = isset($_SESSION['user'])
    && !$isLotExpired
    && $userId !== (int)$lot['author_id']
    && !hasUserBidOnLot($conn, $userId, $lot['id']);

$singleLot = include_template('lot.php', [
    'lot' => $lot,
    'bidsHistory' => $bidsHistory,
    'currentPrice' => $currentPrice,
    'minBid' => $minBid,
    'isFormVisible' => $isFormVisible,
]);

$lotContent = include_template('layout.php', [
    'pageContent' => $singleLot,
    'title' => $lot['lot_title'],
    'categories' => $categoriesFromDB
]);

print $lotContent;
