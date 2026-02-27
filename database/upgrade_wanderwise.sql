-- Add Search History Table
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_query VARCHAR(255) NOT NULL,
    search_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add Coordinates to Cities
ALTER TABLE cities ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8);
ALTER TABLE cities ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8);

-- Add Coordinates to Places
ALTER TABLE places ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8);
ALTER TABLE places ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8);

-- Update Seed Data with Coordinates (approximate)
UPDATE cities SET latitude = 48.8566, longitude = 2.3522 WHERE city_name = 'Paris';
UPDATE cities SET latitude = 40.7128, longitude = -74.0060 WHERE city_name = 'New York';
UPDATE cities SET latitude = 35.6762, longitude = 139.6503 WHERE city_name = 'Tokyo';

-- Places update (Paris)
UPDATE places SET latitude = 48.8584, longitude = 2.2945 WHERE place_name = 'Eiffel Tower';
UPDATE places SET latitude = 48.8606, longitude = 2.3376 WHERE place_name = 'Louvre Museum';

-- Places update (NY)
UPDATE places SET latitude = 40.6892, longitude = -74.0445 WHERE place_name = 'Statue of Liberty';
UPDATE places SET latitude = 40.7829, longitude = -73.9654 WHERE place_name = 'Central Park';

-- Places update (Tokyo)
UPDATE places SET latitude = 35.7147, longitude = 139.7967 WHERE place_name = 'Senso-ji';
UPDATE places SET latitude = 35.6586, longitude = 139.7454 WHERE place_name = 'Tokyo Tower';
