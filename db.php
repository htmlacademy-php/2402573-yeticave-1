<?php

/**
 * Устанавливает соединение с базой данных
 *
 * @param array $config Ассоциативный массив с параметрами БД
 *
 * @return mysqli объект подключения MySQLi
 */

function connectDB(array $config): mysqli
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

/**
 * Выполняет SQL-запрос
 *
 * @param mysqli $conn объект соединения с БД
 * @param string $sql Строка SQL-запроса
 * @return mysqli_result|false Результат запроса или false при ошибке
 */

function getQuery(mysqli $conn, string $sql): mysqli_result|false
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        $error = mysqli_error($conn);
        print("Ошибка MySQL: " . $error);
    }

    return $result;
}


/**
 * Возвращает массив категорий из БД
 *
 * @param mysqli $conn объект соединения с БД
 *
 * @return array Ассоциативный массив категорий
 */
function getCategories(mysqli $conn): array
{
    $categoriesSql = 'SELECT * FROM categories;';
    $res = getQuery($conn, $categoriesSql);

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/**
 * Возвращает массив активных лотов из БД
 *
 * @param mysqli $conn объект соединения с БД
 *
 * @return array Ассоциативный массив лотов
 */
function getLots(mysqli $conn): array
{
    $sql = 'SELECT l.id, l.title AS lot_title, l.starting_price, l.image, l.end_date, c.title AS category_title FROM lots l
                        JOIN categories c ON l.category_id = c.id
                        WHERE l.end_date > NOW()
                    ORDER BY l.created_at DESC
                    LIMIT 9;';
    $res = getQuery($conn, $sql);

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/**
 * Возвращает категорию
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $id id категории
 *
 * @return array|false Ассоциативный массив
 *
 */
function getCategoriesById(mysqli $conn, int $id): ?array
{
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = db_get_prepare_stmt($conn, $sql, [$id]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res) ?: null;
}

/**
 * Возвращает список лотов в определенной категории
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $categoryId id категории
 * @param int $limit количество лотов на странице
 * @param int $offset смещение для пагинации
 *
 * @return array Ассоциативный массив
 *
 */
function getLotsByCategory(mysqli $conn, int $categoryId, int $limit, int $offset): array
{
    $sql = 'SELECT
                l.id,
                l.title AS lot_title,
                l.starting_price,
                l.image,
                l.end_date,
                c.title AS category_title,
                COUNT(b.id) AS bets_count
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON b.lot_id = l.id
            WHERE l.category_id = ? AND l.end_date > NOW()
            GROUP BY l.id
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?;';

    $stmt = db_get_prepare_stmt($conn, $sql, [$categoryId, $limit, $offset]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}


/**
 * Возвращает количество лотов в одпределенной категории
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $categoryId id категории
 *
 * @return int количество лотов
 *
 */
function getLotsCountByCategory(mysqli $conn, int $categoryId): int
{
    $sql = 'SELECT COUNT(*) AS count FROM lots
            WHERE category_id = ? AND end_date > NOW();';
    $stmt = db_get_prepare_stmt($conn, $sql, [$categoryId]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res)['count'];
}

/**
 * Возвращает лот
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $lotId id лота
 *
 * @return array|false Ассоциативный массив
 *
 */
function getLotById(mysqli $conn, $lotId): array|false
{
    $sql = 'SELECT l.id, l.title AS lot_title, l.starting_price, l.description, l.image, l.bidding_step, l.author_id,
            c.title AS category_title, l.end_date, c.symbol_code
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            WHERE l.id = ?';

    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);
    $lot = mysqli_fetch_assoc($res);

    return $lot ?: false;
}

/**
 * Возвращает пользователя по адресу эл. почты
 *
 * @param string $email адрес эл. почты
 * @param mysqli $conn объект соединения с БД
 *
 * @return array|null Ассоциативный массив пользователя или null если не найден
 *
 */
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

/**
 * Проверяет существование адреса email в БД
 *
 * @param string $email адрес эл.почты
 * @param mysqli $conn объект соединения с БД
 *
 * @return bool True, если email существует, иначе false
 *
 */
function isEmailExists(string $email, mysqli $conn): bool
{
    $sql = 'SELECT id FROM users WHERE email = ?';
    $stmt = db_get_prepare_stmt($conn, $sql, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

/**
 * Регистрация нового пользователя
 *
 * @param array $form Массив данных из формы регистрации
 * @param mysqli $conn объект соединения с БД
 *
 * @return bool True если успешно зарегистрирован, иначе false
 *
 */
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

/**
 * Вычисляет текущую цену лота
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $lotId id лота
 *
 * @return int Возвращает текущую цену с учетом минимального шага ставки
 *
 */
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

/**
 * Сохраняет новую ставку в БД
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $lotId id лота
 * @param int $userId id пользователя
 * @param int $bid новая ставка
 *
 * @return bool
 */
function saveBid(mysqli $conn, int $bid, int $lotId, int $userId): bool
{
    $sql = 'INSERT INTO bids (created_at, amount, user_id, lot_id) VALUES (NOW(), ?, ?, ?)';
    $stmt = db_get_prepare_stmt($conn, $sql, [
        $bid,
        $userId,
        $lotId
    ]);
    return mysqli_stmt_execute($stmt);
}


/**
 * Выбирает ставки конкретного пользователя
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $userId id пользователя
 *
 * @return array Возвращает массив ставок
 *
 */
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

/**
 * Выбирает ставки определенного лота
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $lotId id лота
 *
 * @return array Возвращает массив лотов
 *
 */
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
 * Возвращает последнюю (максимальную) ставку на лот
 *
 * @param mysqli $conn объект соединения с БД
 * @param int $lotId id лота
 *
 * @return array Ассоциативный массив последней ставки или false, если ставок нет
 *
 */
function getLotsLastBid(mysqli $conn, int $lotId): array|false
{
    $sql = 'SELECT b.*, u.email, u.name FROM bids b
            JOIN users u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY created_at DESC
            LIMIT 1;';
    $stmt = db_get_prepare_stmt($conn, $sql, [$lotId]);
    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        return false;
    }
    $row = mysqli_fetch_assoc($result);
    return $row ?: false;
}

/**
 * Возвращает лоты, у которых нет победителя и не истек срок торгов
 *
 * @param mysqli $conn объект соединения с БД
 *
 * @return array|false Массив лотов или  false, если подходящих нет
 *
 */
function getCurrentNonWinningLots(mysqli $conn): array|false
{
    $sql = 'SELECT * FROM lots
            WHERE winner_id IS NULL
            AND end_date <= NOW();';

    $result = mysqli_query($conn, $sql);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Сохраняет победителя в БД
 *
 * @param mysqli $conn объект соединения с БД
 * @param $winnerId id победителя
 * @param int $lotId id лота
 *
 * @return bool True, если обновление успешно, иначе false
 *
 */
function saveTheWinner(mysqli $conn, int $winnerId, int $lotId): bool
{
    $sql = 'UPDATE lots SET winner_id = ?
            WHERE id = ?;';
    $stmt = db_get_prepare_stmt($conn, $sql, [$winnerId, $lotId]);
    return mysqli_stmt_execute($stmt);
}

/**
 * Добавляет новый лот в БД.
 *
 * @param mysqli $conn Подключение MySQLi.
 * @param array $form Данные формы (ключи: 'lot-name','message','path','lot-rate','lot-date','lot-step','category').
 * @param int $authorId ID автора (пользователя).
 * @return int|false ID вставленного лота или false при ошибке.
 */
function addNewLot(mysqli $conn, int $authorId, array $form): int|false
{
    $sql = 'INSERT INTO lots (title, description, created_at, image, starting_price,
            end_date, bidding_step, author_id, category_id)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)';

    $data = [
        $form['lot-name'] ?? '',
        $form['message'] ?? '',
        $form['path'] ?? '',
        $form['lot-rate'] ?? 0,
        $form['lot-date'] ?? '',
        $form['lot-step'] ?? 0,
        $authorId,
        $form['category'] ?? 0
    ];

    $stmt = db_get_prepare_stmt($conn, $sql, $data);

    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    $newLotId = (int) mysqli_insert_id($conn);
    return $newLotId > 0 ? $newLotId : false;
}

/**
 * Возвращает список лотов по поисковому запросу
 *
 * @param mysqli $conn объект соединения с БД
 * @param string $query поисковый запрос
 * @param int $limit количество лотов на странице
 * @param int $offset смещение для пагинации
 *
 * @return array Ассоциативный массив
 */
function getLotsBySearch(mysqli $conn, string $query, int $limit, int $offset): array
{
    $sql = "SELECT l.id,
                   l.title AS lot_title,
                   l.starting_price,
                   l.image,
                   c.title AS category_title,
                   l.created_at,
                   l.end_date,
                   COUNT(b.id) AS bets_count
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bids b ON b.lot_id = l.id
            WHERE MATCH(l.title, l.description) AGAINST(?)
              AND l.end_date > NOW()
            GROUP BY l.id
            ORDER BY l.created_at DESC
            LIMIT ? OFFSET ?;";

    $stmt = db_get_prepare_stmt($conn, $sql, [$query, $limit, $offset]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

/**
 * Возвращает количество лотов по поисковому запросу
 *
 * @param mysqli $conn объект соединения с БД
 * @param string $query поисковый запрос
 *
 * @return int количество лотов
 */
function getLotsCountBySearch(mysqli $conn, string $query): int
{
    $sql = "SELECT COUNT(*) AS count
            FROM lots
            WHERE MATCH(title, description) AGAINST(? IN BOOLEAN MODE)
              AND end_date > NOW();";

    $stmt = db_get_prepare_stmt($conn, $sql, [$query]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    return (int) mysqli_fetch_assoc($res)['count'];
}

/**
 * Проверяет, делал ли пользователь хотя бы одну ставку на конкретный лот.
 *
 * @param mysqli $conn подключение к базе данных
 * @param int $userId ID пользователя
 * @param int $lotId ID лота
 * @return bool true, если пользователь уже сделал ставку, иначе false
 */
function hasUserBidOnLot(mysqli $conn, int $userId, int $lotId): bool
{
    $sql = 'SELECT 1 FROM bids WHERE user_id = ? AND lot_id = ? LIMIT 1';
    $stmt = db_get_prepare_stmt($conn, $sql, [$userId, $lotId]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return (bool)mysqli_fetch_assoc($result);
}


/**
 * Возвращает массив с информацией о стоимости лота
 *
 * @param mysqli $conn подключение к базе данных
 * @param int $lotId ID лота
 *
 * @return array ['currentPrice' => $currentPrice, текущая цена
 * 'minBid' => $minBid, минимальная ставка
 * 'biddingStep' => $biddingStep] шаг ставки
 *
 */
function getLotPriceInfo(mysqli $conn, int $lotId): array
{
    // Получаем максимальную ставку
    $sqlBid = 'SELECT MAX(amount) AS max_bid FROM bids WHERE lot_id = ?';
    $stmt = db_get_prepare_stmt($conn, $sqlBid, [$lotId]);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rowBid = mysqli_fetch_assoc($res);

    $lot = getLotById($conn, $lotId);
    $startingPrice = (int)$lot['starting_price'];

    // проверяем шаг ставки до приведения к int
    $biddingStep = $lot['bidding_step'];
    if ($biddingStep === null) {
        $biddingStep = max(1, round($startingPrice * 0.05));
    } else {
        $biddingStep = (int)$biddingStep;
    }

    // Текущая цена = макс ставка, если она есть, иначе стартовая
    $currentPrice = $rowBid['max_bid'] !== null ? (int)$rowBid['max_bid'] : $startingPrice;

    // Минимальная ставка = текущая цена + шаг
    $minBid = $currentPrice + $biddingStep;

    return [
        'currentPrice' => $currentPrice,
        'minBid' => $minBid,
        'biddingStep' => $biddingStep,
    ];
}
