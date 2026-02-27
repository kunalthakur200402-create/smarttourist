-- Database: tourist_guide

CREATE DATABASE IF NOT EXISTS tourist_guide;
USE tourist_guide;

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table: cities
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    image_url VARCHAR(255),
    description TEXT
);

-- Table: places
CREATE TABLE IF NOT EXISTS places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    place_name VARCHAR(100) NOT NULL,
    description TEXT,
    ai_description TEXT,
    image_url VARCHAR(255),
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
);

-- Seed Data: Admin (password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: admin123

-- Seed Data: Cities
INSERT INTO cities (city_name, country, image_url, description) VALUES
('Paris', 'France', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34', 'The City of Light.'),
('New York', 'USA', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9', 'The Big Apple.'),
('Tokyo', 'Japan', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf', 'The bustling capital of Japan.');

-- Seed Data: Places
INSERT INTO places (city_id, place_name, description, image_url) VALUES
(1, 'Eiffel Tower', 'Iron lattice tower on the Champ de Mars.', 'https://images.unsplash.com/photo-1511739001486-91da4f274c8a'),
(1, 'Louvre Museum', 'World''s largest art museum.', 'https://images.unsplash.com/photo-1565099824688-e93eb20fe622'),
(2, 'Statue of Liberty', 'Neoclassical sculpture on Liberty Island.', 'https://images.unsplash.com/photo-1605130284535-11dd9eedc58a'),
(2, 'Central Park', 'Urban park in New York City.', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9'),
(3, 'Senso-ji', 'Ancient Buddhist temple in Asakusa.', 'https://images.unsplash.com/photo-1580422684867-b4d24660d2bd'),
(3, 'Tokyo Tower', 'Communications and observation tower.', 'https://images.unsplash.com/photo-1536098561742-ca998e48cbcc');
