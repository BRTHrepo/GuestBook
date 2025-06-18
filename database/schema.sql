-- database/schema.sql

-- messages tábla létrehozása
CREATE TABLE messages (
                          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                          name VARCHAR(100) NOT NULL,
                          email VARCHAR(255) NOT NULL,
                          message TEXT NOT NULL,
                          rate INT NOT NULL DEFAULT 5 ,
                          is_confirmed TINYINT(1) NOT NULL DEFAULT 0,
                          confirmation_token VARCHAR(64) DEFAULT NULL UNIQUE,
                          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          approved TINYINT(1) NOT NULL DEFAULT 0,
                          edited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


