<?php
// dashboard.php
session_start();
// Later you can add login check here

// Get greeting based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Bowl of Hygea Pharmacy</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#274497',
                        lightgray: '#CDCFD6'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'slide-in': 'slideIn 0.5s ease-out'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-lightgray min-h-screen">

<!-- TOP NAVBAR -->
<header class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-40">
    <div>
        <h1 class="text-2xl font-bold text-primary">Bowl of Hygea Pharmacy</h1>
        <p class="text-xs text-gray-500">Sales & Inventory Management System</p>
    </div>

    <div class="flex items-center gap-6">
        <!-- Search Bar -->
        <div class="hidden md:flex items-center bg-gray-100 rounded-lg px-4 py-2 w-64">
            <input type="text" placeholder="Search products..." class="bg-gray-100 w-full outline-none text-sm">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <!-- Notifications -->
        <div class="relative">
            <button class="relative p-2 text-gray-600 hover:text-primary transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span class="absolute top-1 right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            </button>
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative group">
            <button class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white text-sm font-bold">
                    👤
                </div>
                <span class="text-sm text-gray-600 hidden md:block">Manager</span>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-lightgray rounded-t-lg">Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-lightgray">Settings</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-lightgray">Help</a>
                <hr>
                <a href="login.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-lightgray rounded-b-lg">Logout</a>
            </div>
        </div>
    </div>
</header>

<!-- MAIN CONTENT -->
<div class="flex flex-col lg:flex-row">

    <!-- MAIN DASHBOARD -->
    <main class="flex-1 p-8">
        <!-- Welcome Section -->
        <div class="mb-8 animate-fade-in">
            <h2 class="text-3xl font-bold text-gray-800"><?php echo $greeting; ?>, Admin! 👋</h2>
            <p class="text-gray-600 mt-2">Here's what's happening at your pharmacy today</p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <button class="bg-white rounded-lg shadow p-4 hover:shadow-lg hover:translate-y-minus-1 transition text-left group">
                <div class="text-2xl mb-2">➕</div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-primary">New Sale</p>
            </button>
            <button class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition text-left group">
                <div class="text-2xl mb-2">📦</div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-primary">Add Stock</p>
            </button>
            <button class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition text-left group">
                <div class="text-2xl mb-2">🧾</div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-primary">Receipt</p>
            </button>
            <button class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition text-left group">
                <div class="text-2xl mb-2">⚙️</div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-primary">Settings</p>
            </button>
            <a href="supplier.php"
               class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition text-left group">
                <div class="text-2xl mb-2">🚚</div>
                <p class="text-sm font-semibold text-gray-700 group-hover:text-primary">Supplier Receiving</p>
            </a>
        </div>

        <!-- KEY METRICS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition border-l-4 border-primary">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Sales</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">₱24,580</p>
                        <p class="text-xs text-green-600 mt-2">↑ 12% from yesterday</p>
                    </div>
                    <div class="text-3xl">💰</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition border-l-4 border-orange-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Transactions</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">148</p>
                        <p class="text-xs text-green-600 mt-2">↑ 8 from last day</p>
                    </div>
                    <div class="text-3xl">📊</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition border-l-4 border-blue-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Active Products</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">1,247</p>
                        <p class="text-xs text-green-600 mt-2">↑ 23 new items</p>
                    </div>
                    <div class="text-3xl">📦</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition border-l-4 border-red-400">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm">Low Stock Items</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">15</p>
                        <p class="text-xs text-red-600 mt-2">⚠️ Requires attention</p>
                    </div>
                    <div class="text-3xl">⚡</div>
                </div>
            </div>
        </div>

        <!-- CARDS -->
        <h3 class="text-xl font-bold text-gray-800 mb-4">Main Modules</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

            <!-- SALES -->
            <a href="sales.php"
               class="bg-white rounded-xl shadow p-6 hover:shadow-xl hover:-translate-y-1 transition group cursor-pointer">
                <div class="w-14 h-14 bg-primary text-white rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    🧾
                </div>
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-primary transition">Sales</h3>
                <p class="text-sm text-gray-500">
                    Manage transactions and customer orders
                </p>
                <div class="mt-4 text-xs text-primary font-semibold">View Module →</div>
            </a>

            <!-- INVENTORY -->
            <a href="inventory.php"
               class="bg-white rounded-xl shadow p-6 hover:shadow-xl hover:-translate-y-1 transition group cursor-pointer">
                <div class="w-14 h-14 bg-primary text-white rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    📦
                </div>
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-primary transition">Inventory</h3>
                <p class="text-sm text-gray-500">
                    Track stock levels and manage products
                </p>
                <div class="mt-4 text-xs text-primary font-semibold">View Module →</div>
            </a>

            <!-- REPORTS -->
            <a href="#"
               class="bg-white rounded-xl shadow p-6 hover:shadow-xl hover:-translate-y-1 transition group cursor-pointer">
                <div class="w-14 h-14 bg-primary text-white rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    📊
                </div>
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-primary transition">Reports</h3>
                <p class="text-sm text-gray-500">
                    Generate analytics and business insights
                </p>
                <div class="mt-4 text-xs text-primary font-semibold">View Module →</div>
            </a>

            <!-- SUPPLIER -->
            <a href="supplier.php"
               class="bg-white rounded-xl shadow p-6 hover:shadow-xl hover:-translate-y-1 transition group cursor-pointer">
                <div class="w-14 h-14 bg-primary text-white rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    🚚
                </div>
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-primary transition">Supplier</h3>
                <p class="text-sm text-gray-500">
                    Manage suppliers and purchase orders
                </p>
                <div class="mt-4 text-xs text-primary font-semibold">View Module →</div>
            </a>

        </div>

        <!-- RECENT ACTIVITIES -->
        <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Activities</h3>
        <div class="bg-white rounded-xl shadow p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">✓</div>
                        <div>
                            <p class="font-semibold text-gray-800">Sale Completed</p>
                            <p class="text-xs text-gray-500">2 minutes ago</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-green-600">+₱2,450</span>
                </div>

                <div class="flex items-center justify-between pb-4 border-b">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">⚠️</div>
                        <div>
                            <p class="font-semibold text-gray-800">Low Stock Alert</p>
                            <p class="text-xs text-gray-500">15 minutes ago</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-orange-600">3 Items</span>
                </div>

                <div class="flex items-center justify-between pb-4 border-b">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">📦</div>
                        <div>
                            <p class="font-semibold text-gray-800">Stock Received</p>
                            <p class="text-xs text-gray-500">1 hour ago</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-blue-600">50 Units</span>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">👤</div>
                        <div>
                            <p class="font-semibold text-gray-800">New User Added</p>
                            <p class="text-xs text-gray-500">3 hours ago</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-purple-600">1 User</span>
                </div>
            </div>
        </div>
    </main>

    <!-- RIGHT SIDEBAR -->
    <aside class="w-full lg:w-80 p-6 space-y-6">

        <!-- STOCK ALERTS -->
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Stock Alerts
            </h3>

            <div class="border-l-4 border-red-500 rounded-lg p-3 mb-3 bg-red-50">
                <p class="text-sm font-medium text-gray-700">Low Stock Items</p>
                <p class="text-xs text-gray-500 mt-1">Requires immediate attention</p>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-lg font-bold text-red-500">15 Items</p>
                    <button class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition">View</button>
                </div>
            </div>

            <div class="border-l-4 border-orange-400 rounded-lg p-3 bg-orange-50">
                <p class="text-sm font-medium text-gray-700">Slow Moving Items</p>
                <p class="text-xs text-gray-500 mt-1">Low turnover rate</p>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-lg font-bold text-orange-400">8 Items</p>
                    <button class="text-xs bg-orange-400 text-white px-2 py-1 rounded hover:bg-orange-500 transition">View</button>
                </div>
            </div>
        </div>

        <!-- SEASON TRACKER -->
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v2h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5H4v8a2 2 0 002 2h12a2 2 0 002-2V7h-2v1a1 1 0 11-2 0V7H7v1a1 1 0 11-2 0V7z" clip-rule="evenodd"></path>
                </svg>
                Season Tracker
            </h3>

            <div class="bg-blue-50 border-2 border-primary rounded-lg p-4 mb-3">
                <p class="font-medium text-primary text-sm">Current Season</p>
                <p class="text-2xl font-bold text-primary mt-2">🌧️ Rainy</p>
            </div>

            <div class="bg-gradient-to-br from-primary to-blue-600 text-white rounded-lg p-4">
                <p class="font-semibold">Season Tips</p>
                <ul class="text-xs opacity-90 mt-2 space-y-1">
                    <li>✓ Cold & Flu medications</li>
                    <li>✓ Vitamins & Supplements</li>
                    <li>✓ Pain relievers</li>
                </ul>
            </div>
        </div>

        <!-- PERFORMANCE TRACKER -->
        <div class="bg-white rounded-xl shadow p-5 hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                </svg>
                Performance
            </h3>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600">Daily Sales Goal</span>
                        <span class="font-semibold text-sm">82%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: 82%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600">Inventory Health</span>
                        <span class="font-semibold text-sm">91%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 91%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600">Customer Satisfaction</span>
                        <span class="font-semibold text-sm">88%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 88%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HELP & SUPPORT -->
        <div class="bg-gradient-to-br from-primary to-blue-600 text-white rounded-xl shadow p-5">
            <h3 class="font-semibold mb-2">Need Help?</h3>
            <p class="text-xs opacity-90 mb-4">Our support team is available 24/7 to assist you</p>
            <button class="w-full bg-white text-primary py-2 rounded-lg text-sm font-semibold hover:bg-lightgray transition">
                Contact Support
            </button>
        </div>

    </aside>

</div>

<!-- Footer -->
<footer class="bg-white shadow-inner border-t mt-8">
    <div class="px-6 py-4 text-center text-sm text-gray-500">
        <p>© 2025 Bowl of Hygea Pharmacy. All rights reserved. | <a href="#" class="text-primary hover:underline">Privacy Policy</a> | <a href="#" class="text-primary hover:underline">Terms of Service</a></p>
    </div>
</footer>

<script>
    // Get current time and update it
    function updateTime() {
        const now = new Date();
        const time = now.toLocaleTimeString();
        // You can use this for dynamic updates if needed
    }
    
    // Update on load and every second
    updateTime();
    setInterval(updateTime, 1000);
</script>

</body>
</html>
