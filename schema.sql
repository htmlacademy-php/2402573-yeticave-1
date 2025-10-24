CREATE DATABASE IF NOT EXISTS yeticave
	DEFAULT CHARACTER SET utf8mb4
	DEFAULT COLLATE utf8mb4_general_ci;
 USE yeticave;


CREATE TABLE users (
	id INT PRIMARY KEY AUTO_INCREMENT,
	registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(55) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    password CHAR(64),
    contact_info TEXT
);

CREATE TABLE categories (
	id INT PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(100),
    symbol_code VARCHAR(50) UNIQUE
);

CREATE TABLE lots (
	id INT PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(200),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image VARCHAR(100),
    starting_price DECIMAL(10,2),
    end_date DATETIME,
    bidding_step DECIMAL(10,2),
    author_id INT,
    winner_id INT,
    category_id INT,
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (winner_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE bids (
	id INT PRIMARY KEY AUTO_INCREMENT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10,2),
    user_id INT,
    lot_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (lot_id) REFERENCES lots(id)
);

CREATE INDEX idx_lots_title ON lots(title);

CREATE INDEX idx_lots_end_date ON lots(end_date);

CREATE FULLTEXT INDEX lot_ft_search ON lots(title, description);

