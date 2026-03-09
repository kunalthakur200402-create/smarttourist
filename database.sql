CREATE DATABASE smart_guide;
USE smart_guide;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    google_id VARCHAR(255),
    mobile VARCHAR(20),
    otp VARCHAR(6),
    is_verified BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
);

INSERT INTO admins (username, password)
VALUES ('admin', MD5('admin123'));

CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100),
    place_name VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);