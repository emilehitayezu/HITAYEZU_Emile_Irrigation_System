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

// Handle form submissions
$message = '';
$messageType = '';

// Update user settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle different form submissions
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (!empty($username) && !empty($email)) {
            $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssi", $username, $email, $_SESSION['id']);
                if ($stmt->execute()) {
                    $_SESSION['username'] = $username;
                    $message = "Profile updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error updating profile. Please try again.";
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    } 
    // Add other settings handlers here (password change, notifications, etc.)
}

// Get current user data
$userData = [];
$sql = "SELECT username, email, created_at FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($_SESSION["username"]); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
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
            <a href="soil_analytics.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-700 text-gray-300 hover:text-white">
                <i class="fas fa-seedling w-5"></i>
                <span>Soil Analytics</span>
            </a>
            <a href="settings.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-700 text-white">
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
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-md <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Tabs Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button data-tab="profile" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                            <i class="fas fa-user mr-2"></i>Profile
                        </button>
                        <button data-tab="password" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-key mr-2"></i>Password
                        </button>
                        <button data-tab="notifications" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-bell mr-2"></i>Notifications
                        </button>
                        <button data-tab="api" class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-code mr-2"></i>API Keys
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                <div class="p-6">
                    <!-- Profile Tab -->
                    <div id="profile-tab" class="tab-content active">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Profile Information</h2>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="update_profile" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Tab -->
                    <div id="password-tab" class="tab-content">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Update Password</h2>
                        <form class="space-y-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="tab-content">
                        <h2 class="text-lg font-medium text-gray-900 mb-6">Notification Preferences</h2>
                        <form class="space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="email_notifications" name="email_notifications" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="email_notifications" class="font-medium text-gray-700">Email Notifications</label>
                                        <p class="text-gray-500">Receive email notifications for important updates.</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="alerts" name="alerts" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="alerts" class="font-medium text-gray-700">Alerts</label>
                                        <p class="text-gray-500">Get notified about important system alerts.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- API Keys Tab -->
                    <div id="api-tab" class="tab-content">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-medium text-gray-900">API Keys</h2>
                            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i> New API Key
                            </button>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500">No API keys found. Create your first API key to get started.</p>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Documentation</h3>
                            <p class="text-sm text-gray-500 mb-4">Check out our <a href="#" class="text-blue-600 hover:text-blue-500">API documentation</a> for more information on how to use the API.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
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
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab button
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                this.classList.add('border-blue-500', 'text-blue-600');
                
                // Show active tab content
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>
