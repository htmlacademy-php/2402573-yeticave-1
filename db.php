<?php

function connectDB($config)
{
    $conn = mysqli_connect(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
    );

    if (!$conn) {
        print("Ошибка подключения: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, $config['charset']);

    return $conn;
}

function getQuery($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        $error = mysqli_error($conn);
        print("Ошибка MySQL: " . $error);
    }

    return $result;
}

function getCategories($conn)
{
    $categoriesSql = 'SELECT * FROM categories;';
    $res = getQuery($conn, $categoriesSql);

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function getUserByEmail(string $email, mysqli $conn): ?array
{
    $sql = 'SELECT * FROM users WHERE email = ?';
    $stmt = db_get_prepare_stmt($conn, $sql, [$email]);
    if (!mysqli_stmt_execute($stmt)) {
        return null;
    }
    $result = mysqli_stmt_get_result($stmt);
    return $result ? mysqli_fetch_array($result, MYSQLI_ASSOC) : null;
}

function isEmailExists(string $email, mysqli $conn): bool
{
    $sql = 'SELECT id FROM users WHERE email = ?';
    $stmt = db_get_prepare_stmt($conn, $sql, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

function registerUser(array $form, mysqli $conn): bool {
    $password = password_hash($form['password'], PASSWORD_DEFAULT);
    $sql = 'INSERT INTO users (registration_date, email, name, password, contact_info) VALUES (NOW(), ?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($conn, $sql, [
        $form['email'],
        $form['name'],
        $password,
        $form['message']
    ]);
    return mysqli_stmt_execute($stmt);
}
