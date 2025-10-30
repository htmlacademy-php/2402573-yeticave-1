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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit();
}

$lotId = filter_input(INPUT_POST, 'lot_id', FILTER_VALIDATE_INT);
$lot = getLotById($conn, $lotId);

if (!$lot) {
    http_response_code(404);
    exit('Лот не найден');
}

$costRaw = trim($_POST['cost'] ?? '');
$errors = [];

if ($costRaw === '') {
    $errors['cost'] = 'Введите вашу ставку';
} else {
    $cost = (int)$costRaw;
    $currentPrice = (int)getCurrentPrice($conn, $lotId);

    if ($cost <= $currentPrice) {
        $errors['cost'] = 'Ставка должна быть целым числом и выше текущей цены';
    }
}

if (!empty($errors)) {
    $bidsHistory = getBidsByLot($conn, $lotId);
    $costValue = $_POST['cost'] ?? '';
    renderBidForm($conn, $lot, $errors, $bidsHistory, $costValue);
    exit();
}

$newBid = (int)$cost;

$addedBid = saveBid($conn, $newBid, $lotId, $_SESSION['user']['id']);
$bidsHistory = getBidsByLot($conn, $lotId);

if (!$addedBid) {
    $errors['general'] = 'Ошибка сохранения ставки. Попробуйте ещё раз.';
    $isFormVisible = true;
    renderBidForm($conn, $lot, $errors, $bidsHistory, $costValue, $isFormVisible);

    exit();
}

header("Location: lot.php?id={$lotId}");
exit();
