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
