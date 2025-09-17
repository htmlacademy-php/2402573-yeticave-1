INSERT INTO categories (title, symbol_code) VALUES ('Доски и лыжи', 'boards'), ('Крепления','attachment'),
('Ботинки', 'boots'), ('Одежда', 'clothing'), ('Инструменты', 'tools'), ('Разное', 'other');

INSERT INTO users (name, email, password, contact_info) VALUES ('Василий', 'vasiliy@ya.ru', 'afgrg6jn', 'тел. +7-900-123-67-40'),
('Татьяна', 'tanya@mail.ru', 'bn67grH6jn', 'тел. +7-906-166-67-00'), ('Павел', 'pavel123@gmail.com', '12dfrTT633o', 'тел. +7-967-090-47-16');

INSERT INTO lots (title, image, starting_price, category_id, end_date) VALUES
('2014 Rossignol District Snowboard', 'img/lot-1.jpg', 10999, 1, '2025-11-10'),
('DC Ply Mens 2016/2017 Snowboard', 'img/lot-2.jpg', 15999, 1, '2025-10-07'),
('Крепления Union Contact Pro 2015 года размер L/XL', 'img/lot-3.jpg', 8000, 2, '2025-11-14'),
('Ботинки для сноуборда DC Mutiny Charocal', 'img/lot-4.jpg', 10999, 3, '2025-10-31'),
('Куртка для сноуборда DC Mutiny Charocal', 'img/lot-5.jpg', 7500, 4, '2025-12-14'),
('Маска Oakley Canopy', 'img/lot-6.jpg', 5400, 5, '2025-12-24');

INSERT INTO bids (amount, lot_id, user_id) VALUES
(17000, 2, 1),
(8500, 4, 3),
(12000, 1, 2),
(9000, 5, 1);

-- Вывод всех полей в таблице categories
SELECT * FROM categories;

-- Вывод всех открытых лотов из таблицы lots с сортировкой по самым недавним.
-- Объединение со связанной таблицей categories для отображения названия категории
SELECT l.title, l.starting_price, l.image, l.end_date, c.title AS category FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.end_date > NOW()
ORDER BY l.created_at DESC;

-- Вывод лота по его id по аналогии с предыдущим запросом
SELECT l.title, l.starting_price, l.image, c.title, l.end_date, c.symbol_code FROM lots l
JOIN categories c ON l.category_id = c.id
WHERE l.id = 6;

-- Обновление названия лота по id
UPDATE lots SET  title ='2019 Snowman District Snowboard' WHERE id = 1;

-- Вывод ставки на определенный лот (по id), сортировка по убыванию даты, от самых недавних
-- Объединение с таблицей lots для вывода названия лота
SELECT amount, l.title AS lot_title from bids b
JOIN lots l ON b.lot_id = l.id
WHERE b.lot_id = 2
ORDER BY b.created_at DESC;
