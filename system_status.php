<?php
// system_status.php

// Simulated system checks (you can make these real later)
$systemOnline = true;
$serverStatus = "Operational";
$databaseStatus = "Connected";
$lastUpdated = date("F d, Y h:i A");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Status | Bowl of Hygea Pharmacy</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Custom Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#274497',
                        lightbg: '#CDCFD6'
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-lightbg flex items-center justify-center font-sans">

    <div class="w-full max-w-3xl bg-white rounded-xl shadow-xl overflow-hidden">

        <!-- Header -->
        <div class="bg-primary text-white py-8 text-center">
            <h1 class="text-2xl font-semibold">System Status</h1>
            <p class="text-sm opacity-90">
                Bowl of Hygea Pharmacy Management System
            </p>
        </div>

        <!-- Content -->
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- System Status -->
            <div class="border rounded-lg p-5">
                <h2 class="font-semibold text-gray-700 mb-2">System Status</h2>
                <p class="text-lg font-bold text-green-600">
                    <?= $systemOnline ? "● Online" : "● Offline"; ?>
                </p>
            </div>

            <!-- Server Status -->
            <div class="border rounded-lg p-5">
                <h2 class="font-semibold text-gray-700 mb-2">Server Status</h2>
                <p class="text-lg font-bold text-green-600">
                    <?= $serverStatus; ?>
                </p>
            </div>

            <!-- Database Status -->
            <div class="border rounded-lg p-5">
                <h2 class="font-semibold text-gray-700 mb-2">Database Connection</h2>
                <p class="text-lg font-bold text-green-600">
                    <?= $databaseStatus; ?>
                </p>
            </div>

            <!-- Last Updated -->
            <div class="border rounded-lg p-5">
                <h2 class="font-semibold text-gray-700 mb-2">Last System Check</h2>
                <p class="text-gray-600">
                    <?= $lastUpdated; ?>
                </p>
            </div>

        </div>

        <!-- Notice -->
        <div class="px-8 pb-6">
            <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg text-sm">
                This system is monitored regularly to ensure accurate sales tracking,
                inventory management, and secure pharmacy operations.
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-8 pb-8 flex flex-col md:flex-row gap-4 justify-between items-center">
            <a href="login.php"
               class="px-5 py-2 bg-primary text-white rounded-lg hover:opacity-90 transition">
                Back to Login
            </a>

            <p class="text-xs text-gray-500">
                © 2025 Bowl of Hygea Pharmacy
            </p>
        </div>

    </div>

</body>
</html>
