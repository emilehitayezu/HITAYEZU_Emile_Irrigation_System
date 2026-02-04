<?php
require_once 'config.php';

$result = $conn->query("DESCRIBE sensor_data");
if ($result) {
    echo "<h3>Sensor Data Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
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
    
    // Get sample data
    $sample = $conn->query("SELECT * FROM sensor_data LIMIT 1")->fetch_assoc();
    echo "<h3>Sample Data:</h3>";
    echo "<pre>";
    print_r($sample);
    echo "</pre>";
} else {
    echo "Error describing table: " . $conn->error;
}

$conn->close();
?>
