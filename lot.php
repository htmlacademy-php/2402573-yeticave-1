<?php

require_once 'init.php';

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

$priceInfo = getLotPriceInfo($conn, $lot['id']);
$currentPrice = $priceInfo['currentPrice'];
$biddingStep = $priceInfo['biddingStep'];

if ($biddingStep === null) {
    $biddingStep = max(1, round($currentPrice * 0.05));
}

$minBid = $currentPrice + $biddingStep;

$userId = (int)($_SESSION['user']['id'] ?? 0);

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
    'biddingStep' => $biddingStep
]);

$lotContent = include_template('layout.php', [
    'pageContent' => $singleLot,
    'title' => $lot['lot_title'],
    'categories' => $categoriesFromDB,
    'isAuth' => $isAuth,
    'userName' => $userName
]);

print $lotContent;
