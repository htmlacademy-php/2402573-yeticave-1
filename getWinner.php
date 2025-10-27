<?php

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');
require 'vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$conn = connectDB($db['db']);

$lots = getCurrentNonWinningLots($conn);

if (!$lots) {
    exit();
}

$dsn = 'smtp://helene.reynolds70%40ethereal.email:ENJZp6gxT8dyAEHdwM@smtp.ethereal.email:587?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

foreach ($lots as $lot) {
    $lastBid = getLotsLastBid($conn, $lot['id']);

    if (!$lastBid) {
        continue;
    }

    saveTheWinner($conn, $lastBid['user_id'], $lot['id']);

    $message = new Email();
    $message->to($lastBid['email']);
    $message->from("keks@phpdemo.ru");
    $message->subject("Ваша ставка победила");

    $emailMessage = include_template('email.php', [
        'userName' => $lastBid['name'],
        'lotTitle' => $lot['title'],
        'lotId' => $lot['id']
    ]);

    $message->html($emailMessage);
    $mailer->send($message);
}
