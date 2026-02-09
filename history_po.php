<?php
// po_history.php - Purchase Order history and tracking
session_start();

// DATABASE CONNECTION
// include "../db/db.php";
$conn = null;

// Mock PO data
$mockPOs = [
    ['id' => 101, 'supplier_id' => 1, 'supplier' => 'PharmaCorp Ltd', 'branch' => 'Main Branch', 'date' => '2025-01-27', 'status' => 'Received', 'items' => 5],
    ['id' => 102, 'supplier_id' => 2, 'supplier' => 'MediSupply Inc', 'branch' => 'Main Branch', 'date' => '2025-01-26', 'status' => 'Received', 'items' => 3],
    ['id' => 103, 'supplier_id' => 3, 'supplier' => 'Global Health Pharma', 'branch' => 'Main Branch', 'date' => '2025-01-25', 'status' => 'Received', 'items' => 8],
    ['id' => 104, 'supplier_id' => 1, 'supplier' => 'PharmaCorp Ltd', 'branch' => 'Main Branch', 'date' => '2025-01-24', 'status' => 'Received', 'items' => 2],
    ['id' => 105, 'supplier_id' => 2, 'supplier' => 'MediSupply Inc', 'branch' => 'Main Branch', 'date' => '2025-01-23', 'status' => 'Received', 'items' => 4],
];

$purchase_orders = $mockPOs;

// Apply filters
$supplier_filter = $_GET['supplier'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

if ($supplier_filter) {
    $purchase_orders = array_filter($purchase_orders, fn($po) => strpos(strtolower($po['supplier']), strtolower($supplier_filter)) !== false);
}

if ($status_filter) {
    $purchase_orders = array_filter($purchase_orders, fn($po) => $po['status'] === $status_filter);
}

if ($date_from) {
    $purchase_orders = array_filter($purchase_orders, fn($po) => $po['date'] >= $date_from);
}

if ($date_to) {
    $purchase_orders = array_filter($purchase_orders, fn($po) => $po['date'] <= $date_to);
}

// Get unique suppliers for filter
$suppliers_list = array_unique(array_column($mockPOs, 'supplier'));
sort($suppliers_list);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order History | Bowl of Hygea Pharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#274497',
                        lightgray: '#CDCFD6'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-lightgray min-h-screen flex flex-col">

<div class="flex-1 flex flex-col">
    <!-- HEADER -->
    <header class="bg-white shadow-md px-8 py-6 sticky top-0 z-40">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-primary">Purchase Orders</h1>
                <p class="text-sm text-gray-500">Track all received orders and inventory updates</p>
            </div>
            <a href="suppliers.php" class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-semibold hover:bg-gray-50 transition">
                ← Back
            </a>
        </div>
    </header>

    <div class="p-8 flex-1">
        <!-- FILTERS -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Supplier Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Supplier</label>
                        <select name="supplier" class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                            <option value="">All Suppliers</option>
                            <?php foreach ($suppliers_list as $supp): ?>
                                <option value="<?= htmlspecialchars($supp) ?>" <?= $supplier_filter === $supp ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($supp) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                            <option value="">All Status</option>
                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Received" <?= $status_filter === 'Received' ? 'selected' : '' ?>>Received</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>"
                               class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>"
                               class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition">
                        🔍 Filter
                    </button>
                    <a href="po_history.php" class="border-2 border-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-50 transition">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-primary">
                <p class="text-gray-500 text-sm">Total Purchase Orders</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= count($mockPOs) ?></p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-400">
                <p class="text-gray-500 text-sm">Received Orders</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= count(array_filter($mockPOs, fn($po) => $po['status'] === 'Received')) ?></p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-400">
                <p class="text-gray-500 text-sm">Pending Orders</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= count(array_filter($mockPOs, fn($po) => $po['status'] === 'Pending')) ?></p>
            </div>
        </div>

        <!-- PO TABLE -->
        <?php if (count($purchase_orders) > 0): ?>
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">PO ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Supplier</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Branch</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Items</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($purchase_orders as $po): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-semibold text-primary">#<?= $po['id'] ?></td>
                        <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($po['supplier']) ?></td>
                        <td class="px-6 py-4 text-gray-700"><?= htmlspecialchars($po['branch']) ?></td>
                        <td class="px-6 py-4 text-gray-700"><?= date('M d, Y', strtotime($po['date'])) ?></td>
                        <td class="px-6 py-4 text-gray-700">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                                <?= $po['items'] ?> items
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                <?= $po['status'] === 'Received' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                <?= $po['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="viewDetails(<?= $po['id'] ?>)" class="bg-primary text-white px-3 py-1 rounded text-xs font-semibold hover:opacity-90 transition">
                                View Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl shadow p-12 text-center">
            <div class="text-6xl mb-4">📋</div>
            <h3 class="text-2xl font-bold text-gray-600 mb-2">No Purchase Orders Found</h3>
            <p class="text-gray-500 mb-6">No POs match your filter criteria.</p>
            <a href="po_history.php" class="inline-block bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition">
                Clear Filters
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer class="bg-white shadow-inner border-t mt-auto">
    <div class="px-6 py-4 text-center text-sm text-gray-500">
        <p>© 2025 Bowl of Hygea Pharmacy. All rights reserved.</p>
    </div>
</footer>

<script>
    function viewDetails(poId) {
        alert('View details for PO #' + poId + '\n\nThis feature will show detailed items and allow printing.');
    }
</script>

</body>
</html>
