<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the configuration
require_once 'config.php';

// Function to get the latest sensor data
function getLatestSensorData($limit = 20) {
    global $conn;
    $data = [];
    
    $sql = "SELECT * FROM sensor_data ORDER BY reading_time DESC LIMIT ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
    }
    
    return $data;
}

// Set headers for JSON response
header('Content-Type: application/json');

// Set default limit
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = min(max(1, $limit), 100); // Ensure limit is between 1 and 100

try {
    // Get the latest sensor data
    $data = getLatestSensorData($limit);
    
    // Format the response
    $response = [];
    foreach ($data as $row) {
        $response[] = [
            'id' => (int)$row['id'],
            'moisture' => (float)$row['moisture'],
            'temperature' => (float)$row['temperature'],
            'humidity' => (float)$row['humidity'],
            'reading_time' => $row['reading_time']
        ];
    }
    
    // Return JSON response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_sensor_data.php: " . $e->getMessage());
    
    // Return empty array on error
    echo json_encode([]);
}
?>