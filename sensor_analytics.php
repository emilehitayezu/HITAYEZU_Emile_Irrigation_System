<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include the database configuration
require_once 'config.php';

// Include the export functionality
require_once 'export_functions.php';

// Set default timezone
date_default_timezone_set('UTC');

// Handle export requests
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    
    if (in_array($export_type, ['csv', 'pdf', 'xls'])) {
        exportSensorData($export_type, $start_date, $end_date);
        exit();
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $records_per_page;

// Date filter
$where_conditions = [];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if (!empty($start_date)) {
    $where_conditions[] = "DATE(reading_time) >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_conditions[] = "DATE(reading_time) <= '" . $conn->real_escape_string($end_date) . "'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total number of records for pagination
$count_query = "SELECT COUNT(*) as total FROM sensor_data $where_clause";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get data for current page
$query = "SELECT * FROM sensor_data $where_clause ORDER BY reading_time DESC LIMIT $start_from, $records_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data Analytics - IoT Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Mobile menu button -->
    <button id="menuBtn" class="md:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white shadow-lg">
        <i class="fas fa-bars text-gray-700"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gray-800 text-white p-4 transform md:translate-x-0 z-40">
        <div class="flex items-center justify-between mb-8 pt-4">
            <h1 class="text-xl font-bold">IoT Dashboard</h1>
            <button id="closeMenu" class="md:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="user-info mb-6 p-4 bg-gray-700 rounded-lg">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-indigo-500 flex items-center justify-center text-2xl font-bold">
                <?php echo strtoupper(substr(htmlspecialchars($_SESSION["username"]), 0, 1)); ?>
            </div>
            <p class="text-center font-medium"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
            <p class="text-center text-sm text-gray-300">IoT User</p>
        </div>

        <nav class="space-y-2">
            <a href="welcome.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Main Dashboard</span>
            </a>
            <a href="iot-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-microchip w-5"></i>
                <span>IoT Smart Project</span>
            </a>
            <a href="sensor_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-700 text-white">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="admin-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-user-shield w-5"></i>
                <span>Admin Panel</span>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <div class="md:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <h1 class="text-xl font-semibold text-gray-900">Sensor Data Analytics</h1>
            </div>
        </header>

        <main class="p-6">
            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Filter Data</h3>
                    <div class="space-x-2">
                        <a href="?export=pdf<?php echo !empty($start_date) ? '&start_date='.urlencode($start_date) : ''; ?><?php echo !empty($end_date) ? '&end_date='.urlencode($end_date) : ''; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-file-pdf mr-2"></i> Export PDF
                        </a>
                    </div>
                </div>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <?php if (!empty($start_date) || !empty($end_date)): ?>
                            <a href="sensor_analytics.php" class="ml-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Export Buttons -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex flex-wrap gap-2">
                <a href="?export=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-file-csv mr-2"></i>Export to CSV
                </a>
                <a href="?export=xls&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </a>
                <a href="?export=pdf&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-file-pdf mr-2"></i>Export to PDF
                </a>
            </div>

            <!-- Data Table -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="overflow-x-auto">
                    <table id="sensorTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture (%)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature (°C)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Humidity (%)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($row['reading_time']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($row['moisture'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($row['temperature'], 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo number_format($row['humidity'], 2); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo $start_from + 1; ?></span> to 
                        <span class="font-medium"><?php echo min($start_from + $records_per_page, $total_records); ?></span> of 
                        <span class="font-medium"><?php echo $total_records; ?></span> results
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=1&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                First
                            </a>
                            <a href="?page=<?php echo $page - 1; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        $start_page = max(1, $end_page - 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <a href="?page=<?php echo $i; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-3 py-1 rounded-md border <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                            <a href="?page=<?php echo $total_pages; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Last
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with export buttons
            $('#sensorTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                paging: false,
                searching: false,
                info: false,
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                // Customize CSV export for Edge Impulse compatibility
                customize: function (csv) {
                    // Add a header comment for Edge Impulse
                    var header = "# Edge Impulse compatible CSV\n# This file contains sensor data in a format compatible with Edge Impulse\n# Each row represents a single reading with the following columns:\n# 1. timestamp (ISO 8601 format)\n# 2. moisture (percentage)\n# 3. temperature (Celsius)\n# 4. humidity (%)\n";
                    return header + csv;
                }
            });
        });
    }

    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.getElementById('menuBtn');
        const closeMenu = document.getElementById('closeMenu');
        const sidebar = document.getElementById('sidebar');
        
        // Toggle sidebar on menu button click
        if (menuBtn) {
            menuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            });
        }
        
        // Close sidebar on close button click
        if (closeMenu) {
            closeMenu.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            });
        }
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = sidebar.contains(event.target) || menuBtn.contains(event.target);
            if (!isClickInside && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            }
        });
        
        // Handle window resize
        function handleResize() {
            if (window.innerWidth >= 768) { // md breakpoint
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            }
        }
        
        // Initial check
        handleResize();
        
        // Add resize listener
        window.addEventListener('resize', handleResize);
    });
    </script>
</body>
</html>
