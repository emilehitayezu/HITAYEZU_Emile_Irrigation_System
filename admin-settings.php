<?php
// Initialize the session
session_start();

// Check if the user is logged in and is admin, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin'){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$site_name = $site_email = $items_per_page = "";
$site_name_err = $site_email_err = $items_per_page_err = "";
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate site name
    if (empty(trim($_POST["site_name"]))) {
        $site_name_err = "Please enter a site name.";
    } else {
        $site_name = trim($_POST["site_name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["site_email"]))) {
        $site_email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["site_email"]), FILTER_VALIDATE_EMAIL)) {
        $site_email_err = "Please enter a valid email address.";
    } else {
        $site_email = trim($_POST["site_email"]);
    }
    
    // Validate items per page
    if (empty(trim($_POST["items_per_page"]))) {
        $items_per_page_err = "Please enter the number of items per page.";
    } elseif (!ctype_digit(trim($_POST["items_per_page"]))) {
        $items_per_page_err = "Please enter a valid number.";
    } else {
        $items_per_page = trim($_POST["items_per_page"]);
    }
    
    // Check input errors before updating in database
    if (empty($site_name_err) && empty($site_email_err) && empty($items_per_page_err)) {
        // Here you would typically update these settings in your database
        // For this example, we'll just show a success message
        $success_message = "Settings updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Panel</title>
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
        .tab {
            @apply px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200;
        }
        .tab.active {
            @apply bg-blue-100 text-blue-700;
        }
        .tab:not(.active) {
            @apply text-gray-600 hover:text-gray-900 hover:bg-gray-100;
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
            <a href="admin-dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                <i class="fas fa-tachometer-alt w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin-users.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-800">
                <i class="fas fa-users w-5"></i>
                <span>Manage Users</span>
            </a>
            <a href="admin-settings.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gray-800">
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
                <h1 class="text-xl font-semibold text-gray-900">System Settings</h1>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Search settings...">
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
            <?php if(!empty($success_message)): ?>
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button id="general-tab" class="tab active mr-2" data-tab="general">
                            <i class="fas fa-cog mr-2"></i>General
                        </button>
                        <button id="email-tab" class="tab mr-2" data-tab="email">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </button>
                        <button id="security-tab" class="tab mr-2" data-tab="security">
                            <i class="fas fa-shield-alt mr-2"></i>Security
                        </button>
                        <button id="maintenance-tab" class="tab" data-tab="maintenance">
                            <i class="fas fa-tools mr-2"></i>Maintenance
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- General Settings -->
                    <div id="general-settings" class="tab-content">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="space-y-6">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input type="text" name="site_name" id="site_name" value="<?php echo $site_name; ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php if (!empty($site_name_err)): ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo $site_name_err; ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="site_email" class="block text-sm font-medium text-gray-700">Site Email</label>
                                    <input type="email" name="site_email" id="site_email" value="<?php echo $site_email; ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php if (!empty($site_email_err)): ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo $site_email_err; ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                    <select id="timezone" name="timezone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="UTC">(UTC) Coordinated Universal Time</option>
                                        <option value="America/New_York">(UTC-05:00) Eastern Time</option>
                                        <option value="America/Chicago">(UTC-06:00) Central Time</option>
                                        <option value="America/Denver">(UTC-07:00) Mountain Time</option>
                                        <option value="America/Los_Angeles">(UTC-08:00) Pacific Time</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="date_format" class="block text-sm font-medium text-gray-700">Date Format</label>
                                    <select id="date_format" name="date_format" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="Y-m-d">YYYY-MM-DD (2023-10-05)</option>
                                        <option value="m/d/Y">MM/DD/YYYY (10/05/2023)</option>
                                        <option value="d/m/Y">DD/MM/YYYY (05/10/2023)</option>
                                        <option value="F j, Y">Month D, YYYY (October 5, 2023)</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="items_per_page" class="block text-sm font-medium text-gray-700">Items Per Page</label>
                                    <input type="number" name="items_per_page" id="items_per_page" value="<?php echo $items_per_page; ?>" min="5" max="100" class="mt-1 block w-24 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php if (!empty($items_per_page_err)): ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo $items_per_page_err; ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="pt-5">
                                    <div class="flex justify-end">
                                        <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Reset
                                        </button>
                                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Email Settings (initially hidden) -->
                    <div id="email-settings" class="tab-content hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Email Settings</h3>
                        <p class="text-gray-600 mb-6">Configure your email server settings and email templates.</p>
                        
                        <form class="space-y-6">
                            <div>
                                <label for="mail_driver" class="block text-sm font-medium text-gray-700">Mail Driver</label>
                                <select id="mail_driver" name="mail_driver" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option>SMTP</option>
                                    <option>Mailgun</option>
                                    <option>Sendmail</option>
                                    <option>Mail</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                    <input type="text" id="mail_host" name="mail_host" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="smtp.mailtrap.io">
                                </div>
                                <div>
                                    <label for="mail_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                    <input type="number" id="mail_port" name="mail_port" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="2525">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="mail_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                    <input type="text" id="mail_username" name="mail_username" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="mail_password" class="block text-sm font-medium text-gray-700">SMTP Password</label>
                                    <input type="password" id="mail_password" name="mail_password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="mail_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                                <select id="mail_encryption" name="mail_encryption" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">None</option>
                                    <option value="ssl">SSL</option>
                                    <option value="tls">TLS</option>
                                </select>
                            </div>

                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Test Connection
                                    </button>
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Save Email Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Security Settings (initially hidden) -->
                    <div id="security-settings" class="tab-content hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>
                        <p class="text-gray-600 mb-6">Configure security options and password policies.</p>
                        
                        <form class="space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="password_policy" name="password_policy" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="password_policy" class="font-medium text-gray-700">Enable Password Policy</label>
                                        <p class="text-gray-500">Require strong passwords with minimum length, numbers, and special characters.</p>
                                    </div>
                                </div>

                                <div class="ml-7 space-y-4">
                                    <div class="flex items-center">
                                        <input id="password_min_length" name="password_min_length" type="number" min="6" max="32" value="8" class="w-20 border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <label for="password_min_length" class="ml-2 block text-sm text-gray-700">Minimum password length</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input id="password_require_numbers" name="password_require_numbers" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        <label for="password_require_numbers" class="ml-2 block text-sm text-gray-700">Require numbers</label>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input id="password_require_special" name="password_require_special" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        <label for="password_require_special" class="ml-2 block text-sm text-gray-700">Require special characters</label>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="two_factor_auth" name="two_factor_auth" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="two_factor_auth" class="font-medium text-gray-700">Enable Two-Factor Authentication</label>
                                        <p class="text-gray-500">Add an extra layer of security by requiring a verification code during login.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="login_attempts" name="login_attempts" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="login_attempts" class="font-medium text-gray-700">Limit Login Attempts</label>
                                        <p class="text-gray-500">Temporarily lock accounts after multiple failed login attempts.</p>
                                        <div class="mt-2 ml-6 space-y-2">
                                            <div class="flex items-center">
                                                <input type="number" id="max_attempts" name="max_attempts" min="1" max="10" value="5" class="w-20 border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <label for="max_attempts" class="ml-2 block text-sm text-gray-700">Maximum failed attempts</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="number" id="lockout_minutes" name="lockout_minutes" min="1" max="1440" value="15" class="w-20 border border-gray-300 rounded-md shadow-sm py-1 px-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <label for="lockout_minutes" class="ml-2 block text-sm text-gray-700">Lockout duration (minutes)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Reset
                                    </button>
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Save Security Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Maintenance Settings (initially hidden) -->
                    <div id="maintenance-settings" class="tab-content hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance Mode</h3>
                        <p class="text-gray-600 mb-6">Take your application offline for maintenance.</p>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Maintenance mode will make your application inaccessible to regular users. Only administrators will be able to access the site.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="maintenance_mode" name="maintenance_mode" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="maintenance_mode" class="font-medium text-gray-700">Enable Maintenance Mode</label>
                                    <p class="text-gray-500">When enabled, only administrators can access the site.</p>
                                </div>
                            </div>

                            <div id="maintenance-message-container" class="hidden">
                                <label for="maintenance_message" class="block text-sm font-medium text-gray-700">Maintenance Message</label>
                                <div class="mt-1">
                                    <textarea id="maintenance_message" name="maintenance_message" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md">We're currently performing maintenance. We'll be back online shortly!</textarea>
                                </div>
                            </div>

                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </button>
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Enable Maintenance Mode
                                    </button>
                                </div>
                            </div>
                        </form>
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

        // Tab functionality
        const tabs = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and hide all content
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(content => content.classList.add('hidden'));
                
                // Add active class to clicked tab and show corresponding content
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-settings`).classList.remove('hidden');
            });
        });

        // Toggle maintenance message field
        const maintenanceCheckbox = document.getElementById('maintenance_mode');
        const maintenanceMessageContainer = document.getElementById('maintenance-message-container');
        
        if (maintenanceCheckbox) {
            maintenanceCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    maintenanceMessageContainer.classList.remove('hidden');
                    document.querySelector('button[type="submit"]').textContent = 'Enable Maintenance Mode';
                    document.querySelector('button[type="submit"]').classList.remove('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
                    document.querySelector('button[type="submit"]').classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                } else {
                    maintenanceMessageContainer.classList.add('hidden');
                    document.querySelector('button[type="submit"]').textContent = 'Save Changes';
                    document.querySelector('button[type="submit"]').classList.remove('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500');
                    document.querySelector('button[type="submit"]').classList.add('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
                }
            });
        }
    </script>
</body>
</html>
