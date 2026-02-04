<?php
// Include the database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get POST data
$moisture = isset($_POST['moisture']) ? floatval($_POST['moisture']) : null;
$temperature = isset($_POST['temperature']) ? floatval($_POST['temperature']) : null;
$humidity = isset($_POST['humidity']) ? floatval($_POST['humidity']) : null;

// Validate input
if ($moisture === null || $temperature === null || $humidity === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO sensor_data (moisture, temperature, humidity) VALUES (?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ddd", $moisture, $temperature, $humidity);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Data saved successfully',
                'data' => [
                    'moisture' => $moisture,
                    'temperature' => $temperature,
                    'humidity' => $humidity
                ]
            ]);
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
        
        $stmt->close();
    } else {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>