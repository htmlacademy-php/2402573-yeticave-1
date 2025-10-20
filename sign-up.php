<?php
$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);
$form = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form = $_POST;

    $requiredFields = ['email', 'password', 'name', 'message'];

    foreach ($form as $key => $value) {
        if (empty(trim($value))) {
            $errors[$key] = 'Не заполнено поле ' . $key;
        }
    }

    if (!isset($errors['email'])) {
        $email = $form['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Введите корректный e-mail';
        }
        $sql = 'SELECT id FROM users WHERE email = ?';
        $stmt = db_get_prepare_stmt($conn, $sql, [$email]);
        mysqli_stmt_execute($stmt);
        $resultCheckEmail = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultCheckEmail) > 0) {
            $errors['email'] = 'Пользователь с таким email уже существует';
        }

        if (empty($errors)) {
            $password = password_hash($form['password'], PASSWORD_DEFAULT);

            $sql = 'INSERT INTO users (registration_date, email, name, password, contact_info) VALUES (NOW(), ?, ?, ?, ?)';
            $stmt = db_get_prepare_stmt($conn, $sql, [$form['email'], $form['name'], $password, $form['message']]);
            $insertRes = mysqli_stmt_execute($stmt);
            if ($insertRes) {
                header('Location: login.php');
                exit();
            } else {
                $errors['general'] = 'Ошибка регистрации';
            }
        }
    }
}

$pageContent = include_template('sign-up.php', [
    'errors' => $errors,
    'form' => $form ?? []
]);

$layoutContent = include_template('layout.php', [
    'pageContent'    => $pageContent,
    'categories' => $categoriesFromDB,
    'title'      => 'Регистрация'
]);

print($layoutContent);
