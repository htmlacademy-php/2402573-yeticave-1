<?php

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);
$categoriesIds = array_column($categoriesFromDB, 'id');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lot = $_POST;

    $requiredFields = ['lot-name', 'category', 'message', 'lot-rate', 'lot-date', 'lot-step'];
    $errors = [];

    $rules = [
        'lot-rate' => function ($value) {
            return validatePrice($value);
        },
        'lot-step' => function ($value) {
            return validateStep($value);
        },
        'lot-date' => function ($value) {
            return validateDate($value);
        },
        'category' => function ($value) use ($categoriesIds) {
            return validateCategory($value, $categoriesIds);
        },
    ];

    foreach ($lot as $key => $value) {
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = "Поле обязательно для заполнения";
        }
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
    }

    $errors = array_filter($errors);

    if (empty($_FILES['image']['tmp_name'])) {
        $errors['file'] = 'Вы не загрузили файл';
    } else {
        $tmpName = $_FILES['image']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $tmpName);

        $permittedFileTypes = ['image/jpg', 'image/jpeg', 'image/png'];
        $fileExtensions = [
            'image/jpg'  => 'jpg',
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ];

        if (!in_array($fileType, $permittedFileTypes)) {
            $errors['file'] = 'Загрузите картинку в формате PNG/JPG';
        } else {
            $extension = $fileExtensions[$fileType];
            $filename = uniqid() . '.' . $extension;
            move_uploaded_file($tmpName, 'uploads/' . $filename);
            $lot['path'] = $filename;
        }
    }

    if (count($errors)) {
        $pageContent = include_template('add-lot.php', [
            'lot' => $lot,
            'categories' => $categoriesFromDB,
            'errors' => $errors
        ]);
    } else {
        $sql = 'INSERT INTO lots (title, description, created_at, image, starting_price,
            end_date, bidding_step, author_id, category_id)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, 1, ?)';

        $data = [
            $lot['lot-name'],
            $lot['message'],
            $lot['path'],
            $lot['lot-rate'],
            $lot['lot-date'],
            $lot['lot-step'],
            $lot['category']
        ];

        $stmt = db_get_prepare_stmt($conn, $sql, $data);
        $res = mysqli_stmt_execute($stmt);

        if ($res) {
            $lotId = mysqli_insert_id($conn);
            header('Location: lot.php?id=' . $lotId);
            exit;
        }
    }
} else {
    // первый заход на страницу, форма не отправлена
    $pageContent = include_template('add-lot.php', [
        'categories' => $categoriesFromDB
    ]);
}

$pageLayout = include_template('layout.php', [
    'pageContent' => $pageContent,
    'title' => 'Главная',
    'userName' => 'Анастасия',
    'categories' => $categoriesFromDB,
]);

print $pageLayout;
