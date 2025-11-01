<?php

require_once 'init.php';

if (!isset($_SESSION['user'])) {
    header("Location: /login.php");
    exit('Необходимо войти');
}

$userId = (int)$_SESSION['user']['id'];
$bids = getUserBids($conn, $userId);

foreach ($bids as $key => $bid) {
    $bids[$key]['time'] = getDtRange($bid['end_date']);

    $bids[$key]['expired'] = isBidExpired($bid);

    $bids[$key]['is_winner'] = $bid['winner_id'] === $userId;

    // Если лот завершён, но пользователь не победил, таймер нужно обнулить
    if ($bids[$key]['expired'] && !$bids[$key]['is_winner']) {
        $bids[$key]['time'] = [0, 0];
    }
}

$pageContent = include_template('my-bets.php', [
    'bids' => $bids
]);

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Мои ставки',
    'categories' => getCategories($conn),
    'userName' => $userName,
    'isAuth' => $isAuth
]);

print $pageLayout;
exit();
