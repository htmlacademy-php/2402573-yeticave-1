<?php
session_start();

$db = require __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$conn = connectDB($db['db']);

$isAuth = isset($_SESSION['user']);
$userName = $_SESSION['user']['name'] ?? '';
