<?php
session_start();

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);
$form = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = $_POST;

    $requiredFields = ['email', 'password'];

    foreach ($form as $key => $value) {
        if (empty(trim($value))) {
            $errors[$key] = 'Не заполнено поле ' . $key;
        }
    }

    $email = mysqli_real_escape_string($conn, $form['email']);
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $res = mysqli_query($conn, $sql);

    $user = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;

    if (!count($errors)) {
        $email = mysqli_real_escape_string($conn, $form['email']);
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $res = mysqli_query($conn, $sql);
        $user = $res ? mysqli_fetch_array($res, MYSQLI_ASSOC) : null;

        if ($user) {
            if (password_verify($form['password'], $user['password'])) {
                $_SESSION['user'] = $user;
                header("Location: /index.php");
                exit();
            } else {
                $errors['password'] = 'Вы ввели неверный пароль';
            }
        } else {
            $errors['email'] = 'Такой пользователь не найден';
        }
    }
} else {
    $page_content = include_template('login.php', []);

    if (isset($_SESSION['user'])) {
        header("Location: /index.php");
        exit();
    }
}

$pageContent = include_template('login.php', [
    'errors' => $errors,
    'form' => $form ?? []
]);

$layoutContent = include_template('layout.php', [
    'pageContent'    => $pageContent,
    'categories' => $categoriesFromDB,
    'title'      => 'Вход на сайт'
]);

print($layoutContent);
