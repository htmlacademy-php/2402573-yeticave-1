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

function getLotById(mysqli $conn, $lotId): array|false
{
    $sql = 'SELECT l.id, l.title AS lot_title, l.starting_price, l.description, l.image, c.title AS category_title, l.end_date, c.symbol_code
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.id = ?;';

    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);
    $lot = mysqli_fetch_assoc($res);

    return $lot ?: false;
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

function registerUser(array $form, mysqli $conn): bool
{
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

function getCurrentPrice(mysqli $conn, int $lotId): int
{
    // получаем максимальную ставку из таблицы ставок
    $sqlBid = 'SELECT MAX(amount) AS max_bid FROM bids WHERE lot_id = ?';
    $stmt = db_get_prepare_stmt($conn, $sqlBid, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowBid = mysqli_fetch_assoc($result);

    // получаем начальную цену и шаг ставки из таблицы lots
    $sql = 'SELECT starting_price, bidding_step FROM lots WHERE id = ?';
    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowLot = mysqli_fetch_assoc($result);

    // если существует макс ставка - она текущая цена,
    // если ставок не было - текущая цена = начальной цене
    $basePrice = $rowBid['max_bid'] !== null ? $rowBid['max_bid'] : $rowLot['starting_price'];

    // Минимальная следующая ставка в любом случае = текущая цена + шаг ставки
    $minBid = $basePrice + $rowLot['bidding_step'];

    return (int)$minBid;
}

function saveBid(mysqli $conn, $bid, $lotId, $userId)
{
    $sql = 'INSERT INTO bids (created_at, amount, user_id, lot_id) VALUES (NOW(), ?, ?, ?)';
    $stmt = db_get_prepare_stmt($conn, $sql, [
        $bid,
        $userId,
        $lotId
    ]);
    return mysqli_stmt_execute($stmt);
}

function getUserBids(mysqli $conn, int $userId): array
{
    $sql = 'SELECT
                b.id,
                b.amount,
                b.created_at,
                l.id AS lot_id,
                l.title AS lot_title,
                l.image AS lot_image,
                l.end_date,
                l.winner_id,
                c.title AS category_title,
                u.contact_info AS contact_info
            FROM bids b
            JOIN lots l ON b.lot_id = l.id
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN users u ON l.author_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC';

    $stmt = db_get_prepare_stmt($conn, $sql, [$userId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getMaxBidForLot(mysqli $conn, int $lotId): array|false
{
    $sql = 'SELECT b.user_id, b.amount, b.created_at
            FROM bids b
            WHERE b.lot_id = ?
            ORDER BY b.amount DESC, b.created_at DESC
            LIMIT 1';

    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}


function getBidsByLot(mysqli $conn, int $lotId): array
{
    $sql = "SELECT b.amount, b.created_at, u.name AS user_name
            FROM bids b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.created_at DESC";

    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Возвращает текущую цену лота для отображения на карточке лота
 * (максимальная ставка или стартовая цена, если ставок нет)
 */
function getLotCurrentPrice(mysqli $conn, int $lotId): int
{
    // ищем максимальную ставку
    $sql = 'SELECT MAX(amount) AS max_bid FROM bids WHERE lot_id = ?';
    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);

    if ($row && $row['max_bid'] !== null) {
        return (int)$row['max_bid'];
    }

    // если ставок нет, возвращаем стартовую цену
    $lot = getLotById($conn, $lotId);
    return (int)$lot['starting_price'];
}
