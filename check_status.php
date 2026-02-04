<?php
require_once 'config.php';
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function getLastReadingTime() {
    global $conn;
    $query = "SELECT reading_time, 
                     TIMESTAMPDIFF(SECOND, reading_time, NOW()) as seconds_ago 
              FROM sensor_data 
              ORDER BY reading_time DESC 
              LIMIT 1";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $readingTime = strtotime($row['reading_time']);
        $secondsAgo = (int)$row['seconds_ago'];
        $isRecent = ($secondsAgo <= 60); // Online if data received in last 60 seconds
        
        return [
            'timestamp' => $readingTime,
            'formatted' => date('Y-m-d H:i:s', $readingTime),
            'isRecent' => $isRecent,
            'secondsAgo' => $secondsAgo
        ];
    }
    return null;
}

// Get the last reading
$lastReading = getLastReadingTime();
$status = 'offline';
$lastSeen = 'never';

if ($lastReading) {
    // System is online only if we received data in the last 60 seconds
    $status = $lastReading['isRecent'] ? 'online' : 'offline';
    
    // Format the last seen time
    $seconds = $lastReading['secondsAgo'];
    if ($seconds < 60) {
        $lastSeen = 'just now';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $lastSeen = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } else {
        $lastSeen = date('M j, Y g:i A', $lastReading['timestamp']);
    }
}

// Return the response
echo json_encode([
    'status' => $status,
    'lastSeen' => $lastSeen,
    'lastUpdate' => date('Y-m-d H:i:s'),
    'timestamp' => time()
]);
?>