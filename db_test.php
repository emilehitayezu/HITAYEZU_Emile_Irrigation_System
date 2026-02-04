<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'login_system';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database: " . $db . "<br>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'sensor_data'");
if ($result->num_rows > 0) {
    echo "Table 'sensor_data' exists<br>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $columns = $conn->query("SHOW COLUMNS FROM sensor_data");
    echo "<pre>";
    while($col = $columns->fetch_assoc()) {
        echo $col['Field'] . " - " . $col['Type'] . "<br>";
    }
    echo "</pre>";
    
    // Try to insert test data
    $sql = "INSERT INTO sensor_data (moisture, temperature, humidity) VALUES (50.5, 25.3, 60.2)";
    if ($conn->query($sql) === TRUE) {
        echo "Test data inserted successfully<br>";
    } else {
        echo "Error inserting test data: " . $conn->error . "<br>";
    }
    
    // Show existing data
    $result = $conn->query("SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<h3>Latest Data:</h3>";
        echo "<table border='1'><tr><th>ID</th><th>Moisture</th><th>Temp</th><th>Humidity</th><th>Time</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['moisture'] . "</td>";
            echo "<td>" . $row['temperature'] . "</td>";
            echo "<td>" . $row['humidity'] . "</td>";
            echo "<td>" . $row['reading_time'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No data found in sensor_data table<br>";
    }
} else {
    echo "Table 'sensor_data' does not exist<br>";
}

$conn->close();
?>