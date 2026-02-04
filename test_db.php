<?php
// Include the configuration
require_once 'config.php';

echo "<h1>Database Connection Test</h1>";

// Check connection
if ($conn->connect_error) {
    die("<p style='color:red;'>Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green;'>✅ Connected to database successfully</p>";

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'sensor_data'");
if ($tableCheck->num_rows == 0) {
    echo "<p style='color:red;'>❌ Table 'sensor_data' does not exist</p>";
    
    // Create the table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS sensor_data (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        moisture FLOAT NOT NULL,
        temperature FLOAT NOT NULL,
        humidity FLOAT NOT NULL,
        reading_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTable) === TRUE) {
        echo "<p style='color:green;'>✅ Created 'sensor_data' table successfully</p>";
    } else {
        echo "<p style='color:red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green;'>✅ Table 'sensor_data' exists</p>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE sensor_data");
    if ($result) {
        echo "<h2>Table Structure:</h2>";
        echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                    <th>Default</th>
                    <th>Extra</th>
                </tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show sample data
    $result = $conn->query("SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<h2>Sample Data (Latest 5 records):</h2>";
        echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>ID</th>
                    <th>Moisture</th>
                    <th>Temperature</th>
                    <th>Humidity</th>
                    <th>Reading Time</th>
                </tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['moisture'] . "%</td>";
            echo "<td>" . $row['temperature'] . "°C</td>";
            echo "<td>" . $row['humidity'] . "%</td>";
            echo "<td>" . $row['reading_time'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>⚠️ No data found in sensor_data table</p>";
    }
}

// Close connection
$conn->close();
?>
