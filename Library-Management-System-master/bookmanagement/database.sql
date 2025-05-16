-- /config/library.sql (Database Schema)
CREATE DATABASE library_db;
USE library_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    genre VARCHAR(50),
    cover VARCHAR(255),
    quantity INT,
    availability BOOLEAN DEFAULT TRUE
);

CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    borrow_date DATE,
    due_date DATE,
    return_date DATE NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@example.com', '$2y$10$YatdQ7px3HJisNqfWPkX3u8Vf/w9mlInrBSBV5v3BJu9V9h7LbFUW', 'admin') ON DUPLICATE KEY UPDATE email=email;