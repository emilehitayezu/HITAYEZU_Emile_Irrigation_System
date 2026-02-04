<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Database table
$table = 'sensor_data';

// Table's primary key
$primaryKey = 'id';

// Get all columns from the sensor_data table
$result = $conn->query("SHOW COLUMNS FROM $table");
$columns = array();
$columnIndex = 0;

// Add all columns to the columns array
while($row = $result->fetch_assoc()) {
    $field = $row['Field'];
    // Skip the primary key as it's already included as DT_RowId
    if ($field === 'id') {
        $columns[] = array('db' => 'id', 'dt' => 'DT_RowId');
    }
    $columns[] = array(
        'db' => $field,
        'dt' => $field
    );
    $columnIndex++;
}

// SQL server connection information
$sql_details = array(
    'user' => DB_USERNAME,
    'pass' => DB_PASSWORD,
    'db'   => DB_NAME,
    'host' => DB_SERVER
);

// Include the DataTables server-side processing library
require('ssp.class.php');

// Add date range filter if provided
$where = '';
if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
    $where = "reading_time >= '" . $conn->real_escape_string($_POST['start_date']) . " 00:00:00'";
}

if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
    $where .= ($where ? ' AND ' : '') . "reading_time <= '" . $conn->real_escape_string($_POST['end_date']) . " 23:59:59'";
}

// Add search filter if provided
if (isset($_POST['search']['value']) && !empty($_POST['search']['value'])) {
    $search = $conn->real_escape_string($_POST['search']['value']);
    $searchConditions = array();
    
    // Get all column names for searching
    $result = $conn->query("SHOW COLUMNS FROM $table");
    while($row = $result->fetch_assoc()) {
        $field = $row['Field'];
        // Only search in string and numeric columns
        if (strpos($row['Type'], 'int') === false && 
            strpos($row['Type'], 'float') === false && 
            strpos($row['Type'], 'double') === false && 
            strpos($row['Type'], 'decimal') === false) {
            $searchConditions[] = "`$field` LIKE '%$search%'";
        } else {
            // For numeric fields, search for exact match
            if (is_numeric($search)) {
                $searchConditions[] = "`$field` = $search";
            }
        }
    }
    
    if (!empty($searchConditions)) {
        $searchQuery = '(' . implode(' OR ', $searchConditions) . ')';
        $where .= $where ? " AND $searchQuery" : $searchQuery;
    }
}

// Handle server-side processing request
try {
    // Get total records count
    $totalRecords = 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalRecords = $row['count'];
    }
    
    // Get filtered records count
    $totalFiltered = $totalRecords;
    if (!empty($where)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table WHERE $where");
        if ($result) {
            $row = $result->fetch_assoc();
            $totalFiltered = $row['count'];
        }
    }
    
    // Get the data
    $data = array();
    $query = "SELECT * FROM $table";
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    
    // Add sorting
    if (isset($_POST['order']) && count($_POST['order'])) {
        $orderBy = array();
        foreach ($_POST['order'] as $order) {
            $columnIndex = $order['column'];
            $columnName = $columns[$columnIndex]['db'];
            $direction = $order['dir'] === 'asc' ? 'ASC' : 'DESC';
            $orderBy[] = "`$columnName` $direction";
        }
        if (!empty($orderBy)) {
            $query .= ' ORDER BY ' . implode(', ', $orderBy);
        }
    } else {
        // Default sorting
        $query .= ' ORDER BY reading_time DESC';
    }
    
    // Add pagination
    if (isset($_POST['start']) && $_POST['length'] != -1) {
        $query .= " LIMIT " . intval($_POST['start']) . ", " . intval($_POST['length']);
    }
    
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    // Prepare the response
    $response = array(
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in get_sensor_data_dt.php: " . $e->getMessage());
    
    // Return an error response
    echo json_encode(array(
        "error" => "An error occurred while processing your request.",
        "details" => $e->getMessage()
    ));
}