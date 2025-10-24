<?php
$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

// ранний выход, чтобы не писать логику в else
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderSignUpPage($conn);
}

$form = $_POST;
$requiredFields = ['email', 'password', 'name', 'message'];
$errors = validateRequiredFields($form, $requiredFields);

if (!isset($errors['email']) && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный e-mail';
}

if (!empty($errors)) {
    renderSignUpPage($conn, $errors, $form);
}

if (isEmailExists($form['email'], $conn)) {
    $errors['email'] = 'Пользователь с таким email уже существует';
}

if (!empty($errors)) {
    renderSignUpPage($conn, $errors, $form);
}

if (!registerUser($form, $conn)) {
    $errors['general'] = 'Ошибка регистрации';
    renderSignUpPage($conn, $errors, $form);
}

header('Location: login.php');
exit();
