<?php

session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    die('Доступ запрещён. Только для зарегистрированных пользователей.');
}

$db = require('./config.php');
require_once('./helpers.php');
require_once('./db.php');

$conn = connectDB($db['db']);

$categoriesFromDB = getCategories($conn);
$categoriesIds = array_column($categoriesFromDB, 'id');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    renderAddLotForm($categoriesFromDB, [], [], $_SESSION['user']['name'] ?? '');
}

$lotForm = $_POST;

$requiredFields = [
    'lot-name' => 'Название лота',
    'category' => 'Категория',
    'message' => 'Описание',
    'lot-rate' => 'Начальная цена',
    'lot-date' => 'Дата окончания',
    'lot-step' => 'Шаг ставки'
];

$errors = validateRequiredFields($lotForm, $requiredFields);

$validators = [
    'lot-rate' => 'validatePrice',
    'lot-step' => 'validateStep',
    'lot-date' => 'validateDate',
    'category' => function ($value) use ($categoriesIds) {
        return validateCategory((int)$value, $categoriesIds);
    }
];

$errors = [];

foreach ($requiredFields as $field => $label) {
    $value = $lotForm[$field] ?? '';

    if (trim($value) === '') {
        $errors[$field] = "Поле $label обязательно для заполнения";
        continue;
    }

    if (isset($validators[$field])) {
        $validator = $validators[$field];

        $err = is_string($validator) ? $validator($value) : $validator($value);
        if ($err) {
            $errors[$field] = $err;
        }
    }
}

$errors = array_filter($errors);

if (empty($_FILES['image']['tmp_name'])) {
    $errors['file'] = 'Вы не загрузили файл';
} else {
    $tmpName = $_FILES['image']['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png'];
    $extensions = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    if (!in_array($fileType, $allowedTypes, true)) {
        $errors['file'] = 'Загрузите картинку в формате PNG/JPG';
    } else {
        $ext = $extensions[$fileType];
        $filename = uniqid('', true) . '.' . $ext;
        move_uploaded_file($tmpName, 'uploads/' . $filename);
        $lotForm['path'] = $filename;
    }
}

if (!empty($errors)) {
    renderAddLotForm($categoriesFromDB, $lotForm, $errors, $_SESSION['user']['name'] ?? '');
}

$authorId = (int)($_SESSION['user']['id'] ?? 0);
$newLotId = addNewLot($conn, $authorId, $lotForm);

if ($newLotId === false) {
    $errors['db'] = 'Ошибка при сохранении лота. Попробуйте позже.';
    renderAddLotForm($categoriesFromDB, $lotForm, $errors, $_SESSION['user']['name'] ?? '');
}

header('Location: lot.php?id=' . $newLotId);
exit;
