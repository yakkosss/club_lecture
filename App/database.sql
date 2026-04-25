CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100)NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, 
    role ENUM('ADMIN', 'MODERATOR', 'MEMBER') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    synopsis TEXT,
    cover_path VARCHAR(256),
    release_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
    
CREATE TABLE readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    percentage INT NOT NULL CHECK (percentage BETWEEN 0 AND 100),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);
    
CREATE TABLE readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NULL,
    user_id INT NULL,
    note INT NOT NULL CHECK (note BETWEEN 0 AND 20),
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (book_id, user_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
    
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author INT DEFAULT NULL,
    reading INT DEFAULT NULL,
    comment_text TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comment_state ENUM('APPROVED', 'WAITING', 'REJECTED', 'REPORTED') DEFAULT 'WAITING',
    parent INT DEFAULT NULL,
    level INT CHECK (level BETWEEN 1 AND 3),
    FOREIGN KEY (author) REFERENCES users(id),
    FOREIGN KEY (reading) REFERENCES readings(id),
    FOREIGN KEY (parent) REFERENCES comments(id)
);

DELIMITER //
CREATE TRIGGER define_comment_level

    BEFORE INSERT ON comments
    FOR EACH ROW
    BEGIN
        IF NEW.parent IS NULL THEN
            SET NEW.level = 1;
        ELSE
            SET NEW.level = (SELECT level FROM comments WHERE id = NEW.parent) + 1;
        END IF;
    END//
DELIMITER ;



    