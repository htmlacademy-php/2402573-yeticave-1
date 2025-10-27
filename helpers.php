<?php

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */

function is_date_valid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 *
 * @param int $num Число для форматирования
 *
 * @return string Отформатированная сумма
 */

function formatThePrice(int $num): string
{
    $roundedNum = ceil($num);
    if ($roundedNum < 1000) {
        return "{$roundedNum} ₽";
    }

    return number_format($roundedNum, 0, '', ' ') . ' ₽';
}

/**
 * @param string $date Дата в виде строки
 *
 * @return array<int,int> Массив [часы, минуты] до даты
 */

function getDtRange(string $date): array
{

    $currentDate = time();
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    if ($diff <= 0) {
        return [0, 0];
    }

    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);

    $time = [$hours, $minutes];
    return $time;
}

/**
 * @param int $id ID категории
 * @param int[] $allowedList Список разрешенных id категорий
 *
 * @return string|null Строка с текстом ошибки или null, если ошибки нет
 */

function validateCategory(int $id, array $allowedList): ?string
{

    if ($id === 0) {
        return "Выберите категорию";
    }

    $allowedListFlipped = array_flip($allowedList);
    if (!isset($id, $allowedListFlipped[$id])) {
        return 'Указана несуществующая категория';
    }

    return null;
}

/**
 * @param string $value Цена в виде строки
 *
 * @return string Текст ошибки или null
 */

function validatePrice(string $value): ?string
{
    if (!is_numeric($value) || $value <= 0) {
        return 'Начальная цена должна быть выше нуля';
    }
    return null;
}

/**
 * @param string $value Числовое целое значение
 *
 * @return string Текст ошибки или null
 */

function validateStep(string $value): ?string
{
    if (!ctype_digit($value)) {
        return 'Значение должно быть числовым';
    }
    $intValue = (int) $value;
    if ($intValue <= 0) {
        return 'Ставка должна быть выше нуля';
    }

    return null;
}


/**
 * @param string $value Дата в виде строки
 *
 * @return string Текст ошибки или null
 */

function validateDate(string $value): ?string
{
    if (!is_date_valid($value)) {
        return 'Неверный формат даты';
    }

    $date = date_create($value);
    $cur_date = date_create('today');

    if ($date <= $cur_date) {
        return 'Дата должна быть больше текущей';
    }

    return null;
}

/**
 * Проверяет заполненность обязательных полей формы
 * @param str[]  $form  Поля формы в виде массива строк
 * @param str[] $fields Массив обязательных полей
 * @return array возвращает массив с незаполненными полями
 */

function validateRequiredFields(array $form, array $fields): array
{
    $errors = [];
    foreach ($fields as $field) {
        if (empty(trim($form[$field] ?? ''))) {
            $errors[$field] = 'Не заполнено поле ' . $field;
        }
    }
    return $errors;
}

function renderLoginPage(mysqli $conn, array $errors = [], array $form = [])
{
    $categoriesFromDB = getCategories($conn);
    $pageContent = include_template('login.php', [
        'errors' => $errors,
        'form' => $form
    ]);
    $layoutContent = include_template('layout.php', [
        'pageContent' => $pageContent,
        'categories' => $categoriesFromDB,
        'title' => 'Вход на сайт'
    ]);
    print($layoutContent);
    exit();
}

function renderSignUpPage(mysqli $conn, array $errors = [], array $form = [])
{
    $categoriesFromDB = getCategories($conn);
    $pageContent = include_template('sign-up.php', [
        'errors' => $errors,
        'form' => $form
    ]);
    $layoutContent = include_template('layout.php', [
        'pageContent' => $pageContent,
        'categories' => $categoriesFromDB,
        'title' => 'Регистрация'
    ]);
    print($layoutContent);
    exit();
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = [])
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else if (is_string($value)) {
                $type = 's';
            } else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form(int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template($name, array $data = [])
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

function renderBidForm(mysqli $conn, array $lot, array $errors = [], array $bidsHistory = []): void
{
    $categories = getCategories($conn);

    // текущая цена для отображения
    $currentPrice = getLotCurrentPrice($conn, $lot['id']);

    $pageContent = include_template('lot.php', [
        'lot' => $lot,
        'errors' => $errors,
        'bidsHistory' => $bidsHistory,
        'currentPrice' => $currentPrice
    ]);

    $pageLayout = include_template('layout.php', [
        'pageContent' => $pageContent,
        'title' => $lot['lot_title'],
        'categories' => $categories,
        'userName' => $_SESSION['user']['name'] ?? ''
    ]);

    print $pageLayout;
    exit();
}

function countTimePosted(string $date): string
{
    $timeDiff = time() - strtotime($date);

    // seconds
    if ($timeDiff < 60) {
        return $timeDiff . ' ' . get_noun_plural_form($timeDiff, 'секунда', 'секунды', 'секунд') . ' назад';
    }

    //minutes
    $minutesAgo = floor($timeDiff / 60);
    if ($minutesAgo < 60) {
        return $minutesAgo . ' ' . get_noun_plural_form($minutesAgo, 'минута', 'минуты', 'минут') . ' назад';
    }

    $hoursAgo = floor($timeDiff / 3600);
    if ($hoursAgo < 24) {
        return $hoursAgo . ' ' . get_noun_plural_form($hoursAgo, 'час', 'часа', 'часов') . ' назад';
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if (date('Y-m-d', strtotime($date)) === $yesterday) {
        return 'Вчера в ' . date('H:i', strtotime($date));
    }

    return date('d.m.y в H:i', strtotime($date));
}

function isBidExpired(array $bids): bool
{
    return strtotime($bids['end_date']) < time();
}
