-- Create database if not exists
CREATE DATABASE IF NOT EXISTS iot_monitoring;

-- Use the database
USE iot_monitoring;

-- Create sensor_data table
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moisture FLOAT NOT NULL,
    temperature FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    reading_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (reading_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create a procedure to add test data (optional)
DELIMITER //
CREATE PROCEDURE InsertTestData()
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE start_time TIMESTAMP;
    
    SET start_time = DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    WHILE i < 100 DO
        INSERT INTO sensor_data (moisture, temperature, humidity, reading_time, created_at, updated_at)
        VALUES (
            ROUND(30 + RAND() * 50, 2),  -- Moisture between 30-80%
            ROUND(15 + RAND() * 25, 2),   -- Temp between 15-40°C
            ROUND(30 + RAND() * 50, 2),   -- Humidity between 30-80%
            DATE_ADD(start_time, INTERVAL i * 15 MINUTE),
            NOW(),
            NOW()
        );
        SET i = i + 1;
    END WHILE;
END //
DELIMITER ;

-- Create a user for the application (optional)
CREATE USER IF NOT EXISTS 'iot_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON iot_monitoring.* TO 'iot_user'@'localhost';
FLUSH PRIVILEGES;

-- Call the procedure to insert test data (uncomment if needed)
-- CALL InsertTestData();
