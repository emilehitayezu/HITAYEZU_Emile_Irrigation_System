<?php
// Include the configuration
require_once 'config.php';
require_once 'iot_config.php';

// Function to generate random sensor data for testing
function generateTestData($count = 10) {
    global $iot_conn;
    
    // Clear existing test data
    $iot_conn->query("TRUNCATE TABLE sensor_data");
    
    $startTime = time() - (3600 * 24 * 7); // One week ago
    $endTime = time();
    
    for ($i = 0; $i < $count; $i++) {
        $moisture = rand(20, 90);
        $temperature = 15 + rand(0, 30) + (rand(0, 100) / 100); // 15-45°C
        $humidity = 30 + rand(0, 60); // 30-90%
        $timestamp = date('Y-m-d H:i:s', rand($startTime, $endTime));
        
        $sql = "INSERT INTO sensor_data (moisture, temperature, humidity, reading_time) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $iot_conn->prepare($sql);
        $stmt->bind_param("ddds", $moisture, $temperature, $humidity, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
    
    return $count;
}

// Check if we should generate test data
if (isset($_GET['generate']) && $_GET['generate'] == 'true') {
    $count = isset($_GET['count']) ? (int)$_GET['count'] : 10;
    $generated = generateTestData($count);
    echo "Generated $generated test records.<br>";
}

// Get the latest sensor data
$latestData = getLatestSensorData(5);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sensor Data Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Sensor Data Test</h1>
    
    <?php if (empty($latestData)): ?>
        <p class="error">No sensor data found in the database.</p>
        <p><a href="?generate=true" class="button">Generate Test Data</a></p>
    <?php else: ?>
        <p class="success">Connected to database successfully!</p>
        <p>Latest <?php echo count($latestData); ?> records:</p>
        <table>
            <tr>
                <th>ID</th>
                <th>Time</th>
                <th>Moisture</th>
                <th>Temperature</th>
                <th>Humidity</th>
            </tr>
            <?php foreach ($latestData as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['reading_time']; ?></td>
                <td><?php echo $row['moisture']; ?>%</td>
                <td><?php echo $row['temperature']; ?>°C</td>
                <td><?php echo $row['humidity']; ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p>
            <a href="?generate=true" class="button">Generate More Test Data</a>
            <a href="?clear=true" class="button">Clear All Data</a>
        </p>
    <?php endif; ?>
    
    <h2>API Test</h2>
    <p>Test the JSON API: <a href="get_sensor_data.php?limit=5" target="_blank">get_sensor_data.php?limit=5</a></p>
    
    <h2>Database Status</h2>
    <pre>
    <?php
    // Show database status
    $result = $iot_conn->query("SHOW TABLE STATUS LIKE 'sensor_data'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Table: " . $row['Name'] . "\n";
        echo "Rows: " . $row['Rows'] . "\n";
        echo "Created: " . $row['Create_time'] . "\n";
        echo "Updated: " . $row['Update_time'] . "\n";
    } else {
        echo "Error: Could not get table status. " . $iot_conn->error;
    }
    ?>
    </pre>
</body>
</html>