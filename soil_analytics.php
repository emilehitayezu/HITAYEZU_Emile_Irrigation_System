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

// Set default timezone
date_default_timezone_set('UTC');

// Get soil moisture data
$wet_soil = [];
$moderate_soil = [];
$dry_soil = [];

// Query for wet soil (moisture >= 50%)
$wet_query = "SELECT * FROM sensor_data WHERE moisture >= 50 ORDER BY reading_time DESC";
$wet_result = $conn->query($wet_query);
while($row = $wet_result->fetch_assoc()) {
    $wet_soil[] = $row;
}

// Query for moderate soil (1% <= moisture <= 49%)
$moderate_query = "SELECT * FROM sensor_data WHERE moisture BETWEEN 1 AND 49 ORDER BY reading_time DESC";
$moderate_result = $conn->query($moderate_query);
while($row = $moderate_result->fetch_assoc()) {
    $moderate_soil[] = $row;
}

// Query for dry soil (moisture < 1%)
$dry_query = "SELECT * FROM sensor_data WHERE moisture < 1 ORDER BY reading_time DESC";
$dry_result = $conn->query($dry_query);
while($row = $dry_result->fetch_assoc()) {
    $dry_soil[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soil Analytics - IoT Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-wet {
            background-color: #3b82f6;
            color: white;
        }
        .status-moderate {
            background-color: #f59e0b;
            color: white;
        }
        .status-dry {
            background-color: #ef4444;
            color: white;
        }
    </style>
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
            <a href="sensor_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>
            <a href="soil_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-700 text-white">
                <i class="fas fa-seedling w-5"></i>
                <span>Soil Analytics</span>
            </a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="admin-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-user-shield w-5"></i>
                <span>Admin Panel</span>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg text-red-400 hover:bg-red-900 hover:bg-opacity-20">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Sign Out</span>
            </a>
        </nav>
    </div>
    
    <div class="md:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <h1 class="text-xl font-semibold text-gray-900">Soil Moisture and DHT Data Analytics</h1>
            </div>
        </header>

        <main class="p-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Wet Soil Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Wet Soil</p>
                            <p class="text-2xl font-semibold text-blue-600 wet-count"><?php echo count($wet_soil); ?></p>
                            <p class="text-xs text-gray-500">Moisture ≥ 50%</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-tint text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Moderate Soil Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Moderate Soil</p>
                            <p class="text-2xl font-semibold text-yellow-500 moderate-count"><?php echo count($moderate_soil); ?></p>
                            <p class="text-xs text-gray-500">1% ≤ Moisture ≤ 49%</p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-leaf text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Dry Soil Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dry Soil</p>
                            <p class="text-2xl font-semibold text-red-600 dry-count"><?php echo count($dry_soil); ?></p>
                            <p class="text-xs text-gray-500">Moisture < 1%</p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-sun text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for different soil statuses -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <nav class="flex -mb-px">
                            <button class="tab-button active px-4 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-500" data-tab="wet">
                                Wet Soil (<?php echo count($wet_soil); ?>)
                            </button>
                            <button class="tab-button px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent" data-tab="moderate">
                                Moderate (<?php echo count($moderate_soil); ?>)
                            </button>
                            <button class="tab-button px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent" data-tab="dry">
                                Dry Soil (<?php echo count($dry_soil); ?>)
                            </button>
                        </nav>
                        <div class="flex space-x-2 pr-4" id="export-buttons">
                            <button data-format="csv" data-range="wet" class="export-btn px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600" style="display: none;">Export as CSV</button>
                            <button data-format="xls" data-range="wet" class="export-btn px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600" style="display: none;">Export as XLS</button>
                        </div>
                    </div>
                </div>

                <!-- Wet Soil Table -->
                <div id="wet-tab" class="tab-content p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Humidity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($wet_soil as $reading): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('Y-m-d H:i:s', strtotime($reading['reading_time'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['moisture']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['temperature']; ?>°C</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['humidity']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-wet">Wet</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Moderate Soil Table -->
                <div id="moderate-tab" class="tab-content p-4 hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Humidity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($moderate_soil as $reading): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('Y-m-d H:i:s', strtotime($reading['reading_time'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['moisture']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['temperature']; ?>°C</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['humidity']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-moderate">Moderate</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dry Soil Table -->
                <div id="dry-tab" class="tab-content p-4 hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Humidity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($dry_soil as $reading): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('Y-m-d H:i:s', strtotime($reading['reading_time'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['moisture']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['temperature']; ?>°C</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reading['humidity']; ?>%</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-dry">Dry</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Function to export data in different formats
        function exportData(range, format) {
            // Determine the moisture range based on type
            let filename = '';
            
            switch(range) {
                case 'wet':
                    filename = 'wet_soil_data';
                    break;
                case 'moderate':
                    filename = 'moderate_soil_data';
                    break;
                case 'dry':
                    filename = 'dry_soil_data';
                    break;
                default:
                    return;
            }
            
            // Redirect to export script with parameters
            window.location.href = `export_soil_data.php?range=${range}&format=${format}&filename=${filename}`;
        }
        
        // Function to update export buttons based on active tab
        function updateExportButtons(activeTab) {
            // Update all export buttons to match the active tab
            document.querySelectorAll('.export-btn').forEach(btn => {
                btn.setAttribute('data-range', activeTab);
                btn.style.display = 'inline-block';
            });
        }
        
        // Handle export button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('export-btn')) {
                const range = e.target.getAttribute('data-range');
                const format = e.target.getAttribute('data-format');
                exportData(range, format);
            }
        });
        
        // Toggle mobile menu
        document.getElementById('menuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('active');
        });

        document.getElementById('closeMenu').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
        });

        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabType = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and tabs
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active', 'text-blue-600', 'border-blue-500');
                    btn.classList.add('text-gray-500', 'border-transparent');
                });

                // Add active class to clicked button
                this.classList.add('active', 'text-blue-600', 'border-blue-500');
                this.classList.remove('text-gray-500', 'border-transparent');

                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                });

                // Show selected tab content
                const tabId = tabType + '-tab';
                document.getElementById(tabId).classList.remove('hidden');
                
                // Update export buttons based on active tab
                updateExportButtons(tabType);
            });
        });
        
        // Initialize with the first tab active
        updateExportButtons('wet');

        // Auto-refresh functionality
        function checkForUpdates() {
            $.ajax({
                url: 'get_soil_data.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.updated) {
                        // Update the counts in the cards
                        if (response.wet_count !== undefined) {
                            $('.wet-count').text(response.wet_count);
                            $('.moderate-count').text(response.moderate_count);
                            $('.dry-count').text(response.dry_count);
                            
                            // Update the tab counts
                            $('button[data-tab="wet"]').text(`Wet Soil (${response.wet_count})`);
                            $('button[data-tab="moderate"]').text(`Moderate (${response.moderate_count})`);
                            $('button[data-tab="dry"]').text(`Dry Soil (${response.dry_count})`);
                            
                            // Reload the page to get fresh data
                            location.reload();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error checking for updates:', error);
                }
            });
        }

        // Initialize DataTables on each table
        $(document).ready(function() {
            $('table').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "responsive": true,
                "stateSave": true
            });
            
            // Check for updates every 30 seconds
            setInterval(checkForUpdates, 30000);
        });
    </script>
</body>
</html>