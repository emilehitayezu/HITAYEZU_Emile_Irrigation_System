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

// Get the latest data
$sensorData = getLatestSensorData(20);
$latestData = !empty($sensorData) ? $sensorData[0] : null;

// Prepare data for charts
$chartLabels = [];
$moistureData = [];
$temperatureData = [];
$humidityData = [];

foreach (array_reverse($sensorData) as $data) {
    $chartLabels[] = date('H:i', strtotime($data['reading_time']));
    $moistureData[] = $data['moisture'];
    $temperatureData[] = $data['temperature'];
    $humidityData[] = $data['humidity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard - <?php echo htmlspecialchars($_SESSION["username"]); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .sensor-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .sensor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
            <a href="iot-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-700 text-white">
                <i class="fas fa-microchip w-5"></i>
                <span>IoT Smart Project</span>
            </a>
            <a href="sensor_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>
            <a href="soil_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-seedling w-5"></i>
                <span>Soil Analytics</span>
            </a>
            <a href="settings.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
            <a href="logout.php" class="flex items-center space-x-3 p-3 rounded-lg text-red-400 hover:bg-red-900 hover:bg-opacity-20">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Sign Out</span>
            </a>
        </nav>
    </div>
    <!-- Main Content -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-900">IoT Smart Project Dashboard</h1>
                <div class="flex items-center space-x-6">
                    <div id="system-status" class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">System Status:</span>
                        <div class="flex items-center">
                            <span class="status-indicator h-3 w-3 rounded-full bg-gray-400"></span>
                            <span class="status-text ml-1.5 text-sm font-medium">Checking...</span>
                            <span class="last-seen ml-2 text-xs text-gray-500" style="display: none;"></span>
                        </div>
                    </div>
                    <div id="current-time" class="text-sm text-gray-500"></div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Soil Moisture Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm sensor-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Soil Moisture</p>
                            <p class="text-3xl font-bold text-blue-600" id="moisture-value"><?php echo $latestData ? number_format($latestData['moisture'], 1) . '%' : '--%'; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Last updated: <span id="moisture-time"><?php echo $latestData ? date('H:i', strtotime($latestData['reading_time'])) : '--:--'; ?></span></p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-tint text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <canvas id="moistureChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Temperature Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm sensor-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Temperature</p>
                            <p class="text-3xl font-bold text-red-600" id="temp-value"><?php echo $latestData ? number_format($latestData['temperature'], 1) . '°C' : '--°C'; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Last updated: <span id="temp-time"><?php echo $latestData ? date('H:i', strtotime($latestData['reading_time'])) : '--:--'; ?></span></p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-thermometer-half text-red-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <canvas id="tempChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Humidity Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm sensor-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Humidity</p>
                            <p class="text-3xl font-bold text-green-600" id="humidity-value"><?php echo $latestData ? number_format($latestData['humidity'], 1) . '%' : '--%'; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Last updated: <span id="humidity-time"><?php echo $latestData ? date('H:i', strtotime($latestData['reading_time'])) : '--:--'; ?></span></p>
                        </div>
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-water text-green-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <canvas id="humidityChart" height="80"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Combined Chart -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sensors Data Overview</h3>
                    <div class="h-64">
                        <canvas id="combinedChart"></canvas>
                    </div>
                </div>

                <!-- Status -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-green-100 mr-3">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium">System Status</p>
                                    <p class="text-sm text-gray-500">All systems operational</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">SMART IRRIGATION</span>
                        </div>
                        
                        <div class="p-3 bg-yellow-50 rounded-lg mb-0">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                                <span class="font-medium">Notifications</span>
                            </div>

            <!-- Data Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Sensor Data Logs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sensor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="sensor-logs">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Convert PHP data to JavaScript
        const chartData = {
            labels: <?php echo json_encode($chartLabels); ?>,
            moisture: <?php echo json_encode($moistureData); ?>,
            temperature: <?php echo json_encode($temperatureData); ?>,
            humidity: <?php echo json_encode($humidityData); ?>
        };
        
        // Mobile menu toggle
        const menuBtn = document.getElementById('menuBtn');
        const closeMenu = document.getElementById('closeMenu');
        const sidebar = document.getElementById('sidebar');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        closeMenu.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });

        // Update current time
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
        }

        // Update time immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);

        // Generate random data for demo
        function generateRandomData(count, min, max) {
            return Array.from({length: count}, () => Math.floor(Math.random() * (max - min + 1)) + min);
        }
        
        // Initialize charts with real data
        function initCharts() {
            const timestamps = chartData.labels;
            
            // Moisture Chart
            const moistureCtx = document.getElementById('moistureChart').getContext('2d');
            moistureChart = new Chart(moistureCtx, {
                type: 'line',
                data: {
                    labels: timestamps,
                    datasets: [{
                        label: 'Moisture',
                        data: chartData.moisture,
                        borderColor: 'rgb(37, 99, 235)',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    }
                }
            });

            // Temperature Chart
            const tempCtx = document.getElementById('tempChart').getContext('2d');
            tempChart = new Chart(tempCtx, {
                type: 'line',
                data: {
                    labels: timestamps,
                    datasets: [{
                        label: 'Temperature',
                        data: chartData.temperature,
                        borderColor: 'rgb(220, 38, 38)',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    }
                }
            });

            // Humidity Chart
            const humidityCtx = document.getElementById('humidityChart').getContext('2d');
            humidityChart = new Chart(humidityCtx, {
                type: 'line',
                data: {
                    labels: timestamps,
                    datasets: [{
                        label: 'Humidity',
                        data: chartData.humidity,
                        borderColor: 'rgb(22, 163, 74)',
                        backgroundColor: 'rgba(22, 163, 74, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    }
                }
            });

            // Combined Chart
            const combinedCtx = document.getElementById('combinedChart').getContext('2d');
            combinedChart = new Chart(combinedCtx, {
                type: 'line',
                data: {
                    labels: timestamps,
                    datasets: [
                        {
                            label: 'Moisture',
                            data: chartData.moisture,
                            borderColor: 'rgb(37, 99, 235)',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Temperature',
                            data: chartData.temperature,
                            borderColor: 'rgb(220, 38, 38)',
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            yAxisID: 'y1'
                        },
                        {
                            label: 'Humidity',
                            data: chartData.humidity,
                            borderColor: 'rgb(22, 163, 74)',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.dataset.label === 'Temperature' 
                                            ? context.parsed.y + '°C' 
                                            : context.parsed.y + '%';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Moisture & Humidity (%)'
                            },
                            min: 0,
                            max: 100
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            },
                            min: 0,
                            max: 40
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Fetch latest sensor data from the server
            async function fetchSensorData() {
                try {
                    const response = await fetch('get_sensor_data.php?limit=20');
                    const data = await response.json();
                    
                    if (data && data.length > 0) {
                        // Update the latest data display
                        const latest = data[0];
                        const timeString = new Date(latest.reading_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        document.getElementById('moisture-value').textContent = parseFloat(latest.moisture).toFixed(1) + '%';
                        document.getElementById('temp-value').textContent = parseFloat(latest.temperature).toFixed(1) + '°C';
                        document.getElementById('humidity-value').textContent = parseFloat(latest.humidity).toFixed(1) + '%';
                        
                        document.getElementById('moisture-time').textContent = timeString;
                        document.getElementById('temp-time').textContent = timeString;
                        document.getElementById('humidity-time').textContent = timeString;
                        
                        // Update charts
                        updateCharts(data);
                        
                        // Update logs
                        updateLogs(latest, timeString);
                    }
                } catch (error) {
                    console.error('Error fetching sensor data:', error);
                }
            }
            
            // Update charts with new data
            function updateCharts(data) {
                const labels = [];
                const moistureData = [];
                const tempData = [];
                const humidityData = [];
                
                // Process data in reverse to get chronological order
                [...data].reverse().forEach(item => {
                    labels.push(new Date(item.reading_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
                    moistureData.push(parseFloat(item.moisture));
                    tempData.push(parseFloat(item.temperature));
                    humidityData.push(parseFloat(item.humidity));
                });
                
                // Update chart data
                moistureChart.data.labels = labels;
                moistureChart.data.datasets[0].data = moistureData;
                moistureChart.update();
                
                tempChart.data.labels = labels;
                tempChart.data.datasets[0].data = tempData;
                tempChart.update();
                
                humidityChart.data.labels = labels;
                humidityChart.data.datasets[0].data = humidityData;
                humidityChart.update();
                
                combinedChart.data.labels = labels;
                combinedChart.data.datasets[0].data = moistureData;
                combinedChart.data.datasets[1].data = tempData;
                combinedChart.data.datasets[2].data = humidityData;
                combinedChart.update();
            }
            
        }
        
        // Fetch latest sensor data from the server
        async function fetchSensorData() {
            try {
                const response = await fetch('get_sensor_data.php?limit=20');
                const data = await response.json();
                
                if (data && data.length > 0) {
                    // Update the latest data display
                    const latest = data[0];
                    const timeString = new Date(latest.reading_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    document.getElementById('moisture-value').textContent = parseFloat(latest.moisture).toFixed(1) + '%';
                    document.getElementById('temp-value').textContent = parseFloat(latest.temperature).toFixed(1) + '°C';
                    document.getElementById('humidity-value').textContent = parseFloat(latest.humidity).toFixed(1) + '%';
                    
                    document.getElementById('moisture-time').textContent = timeString;
                    document.getElementById('temp-time').textContent = timeString;
                    document.getElementById('humidity-time').textContent = timeString;
                    
                    // Update charts
                    updateCharts(data);
                    
                    // Update logs
                    updateLogs(latest, timeString);
                }
            } catch (error) {
                console.error('Error fetching sensor data:', error);
            }
        }
        
        // Update charts with new data
        function updateCharts(data) {
            const labels = [];
            const moistureData = [];
            const tempData = [];
            const humidityData = [];
            
            // Process data in reverse to get chronological order
            [...data].reverse().forEach(item => {
                labels.push(new Date(item.reading_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
                moistureData.push(parseFloat(item.moisture));
                tempData.push(parseFloat(item.temperature));
                humidityData.push(parseFloat(item.humidity));
            });
            
            // Update chart data
            moistureChart.data.labels = labels;
            moistureChart.data.datasets[0].data = moistureData;
            moistureChart.update();
            
            tempChart.data.labels = labels;
            tempChart.data.datasets[0].data = tempData;
            tempChart.update();
            
            humidityChart.data.labels = labels;
            humidityChart.data.datasets[0].data = humidityData;
            humidityChart.update();
            
            combinedChart.data.labels = labels;
            combinedChart.data.datasets[0].data = moistureData;
            combinedChart.data.datasets[1].data = tempData;
            combinedChart.data.datasets[2].data = humidityData;
            combinedChart.update();
        }
        
        // Update logs with latest data
        function updateLogs(latestData, timeString) {
                const logs = [
                    { 
                        sensor: 'Soil Moisture', 
                        value: parseFloat(latestData.moisture).toFixed(1) + '%', 
                        status: latestData.moisture < 40 ? 'warning' : 'normal' 
                    },
                    { 
                        sensor: 'Temperature', 
                        value: parseFloat(latestData.temperature).toFixed(1) + '°C', 
                        status: latestData.temperature > 30 ? 'warning' : 'normal' 
                    },
                    { 
                        sensor: 'Humidity', 
                        value: parseFloat(latestData.humidity).toFixed(1) + '%', 
                        status: 'normal' 
                    }
                ];
                
                const logsContainer = document.getElementById('sensor-logs');
                logsContainer.innerHTML = '';
                
                logs.forEach(log => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${timeString}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${log.sensor}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${log.value}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                log.status === 'warning' 
                                    ? 'bg-yellow-100 text-yellow-800' 
                                    : 'bg-green-100 text-green-800'
                            }">
                                ${log.status === 'warning' ? 'Warning' : 'Normal'}
                            </span>
                        </td>
                    `;
                    logsContainer.prepend(row);
                });
            }

        // Declare chart variables in global scope
        let moistureChart, tempChart, humidityChart, combinedChart;

        // Initialize everything when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            initCharts();
            
            // Initial data fetch
            fetchSensorData();
            
            // Update data every 5 seconds
            setInterval(fetchSensorData, 5000);
            
            // Also update time every second
            updateTime();
            setInterval(updateTime, 1000);
        });
    </script>

    <script>
    // System status functionality
    function updateSystemStatus() {
        fetch('check_status.php?t=' + new Date().getTime()) // Add timestamp to prevent caching
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const statusElement = document.getElementById('system-status');
                if (!statusElement) {
                    console.error('Status element not found');
                    return;
                }

                // Clear previous content
                statusElement.innerHTML = '';

                // Create status indicator
                const indicator = document.createElement('span');
                indicator.className = 'status-indicator w-3 h-3 rounded-full inline-block mr-2 ' + 
                                    (data.status === 'online' ? 'bg-green-500' : 'bg-red-500');

                // Create status text
                const statusText = document.createElement('span');
                statusText.className = 'status-text font-medium';
                statusText.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                // Create last seen text (only show when offline)
                if (data.status === 'offline') {
                    const lastSeen = document.createElement('span');
                    lastSeen.className = 'last-seen text-xs text-gray-500 ml-2';
                    lastSeen.textContent = `(Last seen: ${data.lastSeen || 'unknown'})`;
                    statusElement.appendChild(lastSeen);
                }

                // Add elements to status container
                statusElement.prepend(indicator);
                statusElement.appendChild(statusText);

                // Update the last checked time
                const now = new Date();
                const timeElement = document.getElementById('status-time');
                if (timeElement) {
                    timeElement.textContent = now.toLocaleTimeString();
                }

                console.log(`System status updated: ${data.status} at ${now.toLocaleTimeString()}`);
            })
            .catch(error => {
                console.error('Error checking system status:', error);
                const statusElement = document.getElementById('system-status');
                if (statusElement) {
                    statusElement.innerHTML = `
                        <span class="text-yellow-500 flex items-center">
                            <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                            <span>Connection Error</span>
                        </span>
                    `;
                }
            });
    }

    // Check status every 10 seconds
    let statusCheckInterval = setInterval(updateSystemStatus, 10000);

    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Add smooth transition for status indicator
        const style = document.createElement('style');
        style.textContent = `
            .status-indicator {
                transition: background-color 0.3s ease;
            }
            #system-status {
                display: flex;
                align-items: center;
            }
        `;
        document.head.appendChild(style);

        // Initial status check
        updateSystemStatus();
        
        // Also update status when the page becomes visible again
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateSystemStatus();
            }
        });
    });
    </script>
</body>
</html>
