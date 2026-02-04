<?php
require_once 'config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get list of tables
$result = $conn->query("SHOW TABLES");

if ($result) {
    echo "<h2>Tables in database:</h2>";
    echo "<ul>";
    while($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . $conn->error;
}

// Check if sensor_data table exists
$table_check = $conn->query("SHOW TABLES LIKE 'sensor_data'");
if($table_check->num_rows == 1) {
    echo "<p style='color:green;'>sensor_data table exists!</p>";
    
    // Show structure of sensor_data table
    echo "<h3>Structure of sensor_data table:</h3>";
    $structure = $conn->query("DESCRIBE sensor_data");
    if($structure) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $structure->fetch_assoc()) {
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
        
        // Show sample data (first 5 rows)
        echo "<h3>Sample data (first 5 rows):</h3>";
        $sample = $conn->query("SELECT * FROM sensor_data ORDER BY id DESC LIMIT 5");
        if($sample && $sample->num_rows > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            // Table header
            echo "<tr>";
            $fields = $sample->fetch_fields();
            foreach($fields as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            // Table data
            while($row = $sample->fetch_row()) {
                echo "<tr>";
                foreach($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red;'>No data found in sensor_data table or table is empty.</p>";
            if($conn->error) {
                echo "<p>Error: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p style='color:red;'>Error getting table structure: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:red;'>sensor_data table does not exist in the database.</p>";
}

$conn->close();
?>
