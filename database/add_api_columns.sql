-- Update cities table to include API-fetched data
ALTER TABLE cities ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8);
ALTER TABLE cities ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8);
ALTER TABLE cities ADD COLUMN IF NOT EXISTS weather_temp INT;
ALTER TABLE cities ADD COLUMN IF NOT EXISTS weather_desc VARCHAR(255);
ALTER TABLE cities ADD COLUMN IF NOT EXISTS directions TEXT;
