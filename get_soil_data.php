<?php
session_start();
require_once 'config.php';

// Get the latest timestamp from the database
$latest_query = "SELECT MAX(reading_time) as latest_time FROM sensor_data";
$result = $conn->query($latest_query);
$latest_time = $result->fetch_assoc()['latest_time'];

// Check if we have a stored timestamp in the session
$last_checked = $_SESSION['last_checked'] ?? null;

// Prepare the response
$response = ['updated' => false];

if ($last_checked === null || $last_checked !== $latest_time) {
    $response['updated'] = true;
    
    // Get the latest counts
    $wet_count = $conn->query("SELECT COUNT(*) as count FROM sensor_data WHERE moisture >= 50")->fetch_assoc()['count'];
    $moderate_count = $conn->query("SELECT COUNT(*) as count FROM sensor_data WHERE moisture BETWEEN 1 AND 49")->fetch_assoc()['count'];
    $dry_count = $conn->query("SELECT COUNT(*) as count FROM sensor_data WHERE moisture < 1")->fetch_assoc()['count'];
    
    $response['wet_count'] = $wet_count;
    $response['moderate_count'] = $moderate_count;
    $response['dry_count'] = $dry_count;
    
    // Update the stored timestamp
    $_SESSION['last_checked'] = $latest_time;
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
