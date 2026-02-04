<?php
// Initialize the session
session_start();

// Check if the user is logged in and is admin, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo htmlspecialchars($_SESSION["username"]); ?></title>
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
    <button id="menuBtn" class="md:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-gray-800 text-white">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 w-64 bg-gray-900 text-white p-4 z-40">
        <div class="flex items-center justify-between mb-8 pt-4">
            <h1 class="text-2xl font-bold">Admin Panel</h1>
        </div>
        <nav class="space-y-2">
            <a href="admin-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-800">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin-users.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                <i class="fas fa-users w-5"></i>
                <span>Manage Users</span>
            </a>
            <a href="admin-settings.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                <i class="fas fa-cog w-5"></i>
                <span>System Settings</span>
            </a>
            <a href="welcome.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                <i class="fas fa-home w-5"></i>
                <span>Main Dashboard</span>
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
                <h1 class="text-xl font-semibold text-gray-900">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Search...">
                    </div>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr(htmlspecialchars($_SESSION["username"]), 0, 1)); ?>
                            </div>
                            <span class="hidden md:inline text-gray-700"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            <a href="admin-settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl text-white p-6 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
                        <p class="text-blue-100">Here's what's happening with your system today.</p>
                    </div>
                    <button class="mt-4 md:mt-0 bg-white text-blue-700 hover:bg-blue-50 px-6 py-2 rounded-lg font-medium">
                        View Reports
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Users -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Users</p>
                            <p class="text-2xl font-bold">1,248</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-green-600 text-sm font-medium">
                            <i class="fas fa-arrow-up"></i> 12.5%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">vs last month</span>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Active Sessions</p>
                            <p class="text-2xl font-bold">42</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-user-clock text-green-600"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-green-600 text-sm font-medium">
                            <i class="fas fa-arrow-up"></i> 5.2%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">vs yesterday</span>
                    </div>
                </div>

                <!-- System Health -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">System Health</p>
                            <p class="text-2xl font-bold">98.5%</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-heartbeat text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-green-600 text-sm font-medium">
                            <i class="fas fa-check"></i> Operational
                        </span>
                    </div>
                </div>

                <!-- Storage Used -->
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Storage Used</p>
                            <p class="text-2xl font-bold">65%</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-database text-purple-600"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">325GB of 500GB used</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Recent Activity -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Recent Activity</h3>
                        <button class="text-sm text-blue-600 hover:text-blue-800">View All</button>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">New user registered</p>
                                <p class="text-sm text-gray-500">John Doe (john@example.com) just signed up</p>
                                <p class="text-xs text-gray-400">5 minutes ago</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-upload text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Data export completed</p>
                                <p class="text-sm text-gray-500">User data export to CSV was successful</p>
                                <p class="text-xs text-gray-400">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Warning: High server load</p>
                                <p class="text-sm text-gray-500">CPU usage reached 85% on server-01</p>
                                <p class="text-xs text-gray-400">5 hours ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Add New User</p>
                                <p class="text-xs text-gray-500">Create a new user account</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-cog text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">System Settings</p>
                                <p class="text-xs text-gray-500">Configure system preferences</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-file-export text-purple-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Export Data</p>
                                <p class="text-xs text-gray-500">Export user data to CSV/Excel</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fas fa-shield-alt text-red-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Security Audit</p>
                                <p class="text-xs text-gray-500">Run security checks</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                <h3 class="text-lg font-semibold mb-4">System Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 border rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Web Server</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Online</span>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500">Apache/2.4.41</p>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-green-500 h-1.5 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Database</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Online</span>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500">MySQL 8.0.26</p>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-blue-500 h-1.5 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">PHP</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">v8.1.0</span>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500">Memory: 128M / 256M</p>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-yellow-500 h-1.5 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Storage</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">65% used</span>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500">325GB of 500GB</p>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                <div class="bg-purple-500 h-1.5 rounded-full" style="width: 65%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('menuBtn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuBtn = document.getElementById('menuBtn');
            if (!sidebar.contains(event.target) && event.target !== menuBtn) {
                sidebar.classList.remove('active');
            }
        });

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            const clockElement = document.getElementById('clock');
            const dateElement = document.getElementById('date');
            
            if (clockElement) clockElement.textContent = timeString;
            if (dateElement) dateElement.textContent = dateString;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
</body>
</html>
