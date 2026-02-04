<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($_SESSION["username"]); ?></title>
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
            <h1 class="text-xl font-bold">Dashboard</h1>
            <button id="closeMenu" class="md:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="user-info mb-6 p-4 bg-gray-700 rounded-lg">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-indigo-500 flex items-center justify-center text-2xl font-bold">
                <?php echo strtoupper(substr(htmlspecialchars($_SESSION["username"]), 0, 1)); ?>
            </div>
            <p class="text-center font-medium"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
            <p class="text-center text-sm text-gray-300">Member</p>
        </div>

        <nav class="space-y-2">
            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-700 text-white">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-user w-5"></i>
                <span>Profile</span>
            </a>
            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-cog w-5"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-chart-line w-5"></i>
                <span>Analytics</span>
            </a>
            <a href="iot-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-microchip w-5"></i>
                <span>IoT Smart Project</span>
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
                <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <i class="fas fa-bell text-gray-500 text-xl cursor-pointer"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </div>
                    <div id="current-time" class="hidden md:block text-sm text-gray-500"></div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6">
            <!-- IoT Smart Irrigation Hero -->
            <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-lg shadow-sm p-6 mb-6 text-white">
                <div class="flex flex-col md:flex-row md:items-center">
                    <div class="md:w-2/3">
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Smart Irrigation System</h2>
                        <p class="text-blue-100 mb-4">Monitor and control your irrigation system from anywhere, anytime. Real-time data and automated watering for optimal plant growth.</p>
                        <div class="flex space-x-3">
                            <a href="iot-dashboard.php" class="px-4 py-2 bg-white text-blue-600 rounded-md font-medium hover:bg-blue-50 transition-colors">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="#" class="px-4 py-2 border border-white text-white rounded-md font-medium hover:bg-white hover:bg-opacity-10 transition-colors">
                                <i class="fas fa-play-circle mr-2"></i>Quick Start
                            </a>
                        </div>
                    </div>
                    <div class="hidden md:block md:w-1/3">
                        <img src="https://cdn-icons-png.flaticon.com/512/2933/2933245.png" alt="Smart Irrigation" class="w-full h-auto opacity-90">
                    </div>
                </div>
            </div>

            <!-- System Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">System Status</p>
                            <p class="text-2xl font-semibold text-gray-900">Active</p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Water Flow</p>
                            <p class="text-2xl font-semibold text-gray-900">5 L/min</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-tint text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Soil Moisture</p>
                            <p class="text-2xl font-semibold text-gray-900"> Click on IoT Smart Project</p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-cloud-rain text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
            
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-microchip text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Activity -->
    <script>
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
                minute: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
        }

        // Update time immediately and then every minute
        updateTime();
        setInterval(updateTime, 60000);

        // Sample chart
        const ctx = document.createElement('canvas');
        document.querySelector('.chart-container')?.appendChild(ctx);
        
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Active Users',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(79, 70, 229)',
                        tension: 0.1,
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
