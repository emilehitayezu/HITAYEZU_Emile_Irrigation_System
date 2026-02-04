<?php
// Start session and include config
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied. Please log in.");
}

// Get parameters
$range = isset($_GET['range']) ? $_GET['range'] : '';
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
$filename = isset($_GET['filename']) ? $_GET['filename'] : 'soil_data';

// Validate range parameter
$valid_ranges = ['wet', 'moderate', 'dry'];
if (!in_array($range, $valid_ranges)) {
    die("Invalid range specified. Must be one of: " . implode(', ', $valid_ranges));
}

// Set up SQL query based on range
$sql = "SELECT * FROM sensor_data WHERE ";
switch ($range) {
    case 'wet':
        $sql .= "moisture >= 50";
        break;
    case 'moderate':
        $sql .= "moisture BETWEEN 1 AND 49";
        break;
    case 'dry':
        $sql .= "moisture < 1";
        break;
}
$sql .= " ORDER BY reading_time DESC";

// Execute query
$result = $conn->query($sql);

// Check if we have data
if ($result->num_rows === 0) {
    die("No data found for the selected range.");
}

// Process data for export
$data = [];
$headers = ['Timestamp', 'Moisture (%)', 'Temperature (°C)', 'Humidity (%)', 'Status'];

while ($row = $result->fetch_assoc()) {
    // Determine status based on moisture level
    $status = '';
    if ($row['moisture'] >= 50) {
        $status = 'Wet';
    } elseif ($row['moisture'] >= 1) {
        $status = 'Moderate';
    } else {
        $status = 'Dry';
    }
    
    $data[] = [
        $row['reading_time'],
        $row['moisture'],
        $row['temperature'],
        $row['humidity'],
        $status
    ];
}

// Handle different export formats
switch ($format) {
    case 'xls':
        exportXLS($data, $headers, $filename . '.xls');
        break;
    case 'csv':
    default:
        exportCSV($data, $headers, $filename . '.csv');
        break;
}

/**
 * Export data as CSV file
 */
function exportCSV($data, $headers, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel compatibility
    fputs($output, "\xEF\xBB\xBF");
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Export data as XLS file (using HTML table for simplicity)
 */
function exportXLS($data, $headers, $filename) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Soil Data</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
    echo '<body><table>';
    
    // Add headers
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Add data rows
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table></body></html>';
    exit;
}
?>
