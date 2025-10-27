<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);


if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit('Необходимо войти');
}

$userId = $_SESSION['user']['id'];

$bids = getUserBids($conn, $userId);

$maxBids = [];
foreach ($bids as $bid) {
    $lotId = $bid['lot_id'];
    $maxBids[$lotId] = getMaxBidForLot($conn, $lotId);
}

// Обрабатываем каждую ставку пользователя
foreach ($bids as $key => $bid) {
    $time = getDtRange($bid['end_date']);
    $bids[$key]['time'] = $time;

    $bids[$key]['expired'] = isBidExpired($bid);

    $maxBid = $maxBids[$bid['lot_id']] ?? null;

    // текущий пользователь — победитель, если он автор максимальной ставки по завершённому лоту
    // логика определения победителя требуется в следующем задании
    $bids[$key]['is_winner'] = $bid['expired']
        && $maxBid
        && (int)$maxBid['user_id'] === (int)$userId;
}

$pageContent = include_template('my-bets.php', [
    'bids' => $bids
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Мои ставки',
    'categories' => getCategories($conn),
    'username' => $_SESSION['user']['name'],
]);

print $pageLayout;
exit();
