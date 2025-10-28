<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // если пользователь уже зашел, загружаем главную страницу
    if (isset($_SESSION['user'])) {
        header("Location: /index.php");
        exit();
    }
    // если нет, то загружаем форму входа на сайт
    renderLoginPage($conn);
}

$form = $_POST;
$requiredFields = ['email', 'password'];
// проверка заполненности полей
$errors = validateRequiredFields($form, $requiredFields);

// если поле почты не пустое и формат неверен - сообщение об ошибке
if (!isset($errors['email']) && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный e-mail';
}

// если есть ошибки, грузим форму с сообщениями о них
if (!empty($errors)) {
    renderLoginPage($conn, $errors, $form);
}

$user = getUserByEmail($form['email'], $conn);

if (!$user) {
    $errors['email'] = 'Неверный логин или email';
} elseif (!password_verify($form['password'], $user['password'])) {
    $errors['password'] = 'Неверный логин или email';
}

// Если есть ошибки после попытки входа, выводим их с формой
if (!empty($errors)) {
    renderLoginPage($conn, $errors, $form);
}

// Успешный вход и редирект на главную
$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email']
];

header("Location: /index.php");
exit();
