<?php
// add_supplier.php - Add new supplier
session_start();

// DATABASE CONNECTION
// include "../db/db.php";
$conn = null;

$message = '';
$message_type = '';

// HANDLE FORM SUBMISSION
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    // Validation
    if (empty($name)) {
        $message = "❌ Supplier name is required.";
        $message_type = "error";
    } elseif (empty($phone)) {
        $message = "❌ Contact number is required.";
        $message_type = "error";
    } else {
        if ($conn) {
            $stmt = $conn->prepare(
                "INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssss", $name, $contact_person, $phone, $email, $address, $status);
            
            if ($stmt->execute()) {
                $message = "✅ Supplier added successfully!";
                $message_type = "success";
                // Clear form
                $_POST = [];
            } else {
                $message = "❌ Error adding supplier. Try again.";
                $message_type = "error";
            }
        } else {
            $message = "✅ Supplier added successfully! (Mock - database not connected)";
            $message_type = "success";
            $_POST = [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Supplier | Bowl of Hygea Pharmacy</title>
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

    <!-- HEADER -->
    <header class="bg-white shadow-md px-8 py-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-primary">Add Supplier</h1>
                <div class="flex gap-2 text-sm text-gray-500 mt-2">
                    <a href="dashboard.php" class="hover:text-primary">Dashboard</a>
                    <span>></span>
                    <a href="suppliers.php" class="hover:text-primary">Suppliers</a>
                    <span>></span>
                    <span>Add Supplier</span>
                </div>
            </div>
            <a href="suppliers.php" class="border-2 border-primary text-primary px-6 py-2 rounded-lg font-semibold hover:bg-gray-50 transition">
                Back to Suppliers
            </a>
        </div>
    </header>

    <div class="p-8 flex-1">
        <!-- ALERT MESSAGE -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg text-sm font-medium
                <?= $message_type === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- FORM CARD -->
        <div class="bg-white rounded-xl shadow p-8 max-w-2xl">
            <form method="POST" class="space-y-6">

                <!-- Supplier Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Supplier Name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="e.g., PharmaCorp Ltd"
                           class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Contact Person -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Person</label>
                    <input type="text" name="contact_person" value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>"
                           placeholder="e.g., John Smith"
                           class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number *</label>
                    <input type="tel" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           placeholder="e.g., +1-555-0101"
                           class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="e.g., john@pharmacorp.com"
                           class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                    <textarea name="address" rows="3" placeholder="e.g., 123 Medical Ave, City"
                              class="w-full border-2 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Status</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="Active" checked class="w-4 h-4">
                            <span class="text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="Inactive" class="w-4 h-4">
                            <span class="text-gray-700">Inactive</span>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-6 border-t">
                    <button type="submit" class="flex-1 bg-primary text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                        💾 Save Supplier
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-white shadow-inner border-t mt-auto">
        <div class="px-6 py-4 text-center text-sm text-gray-500">
            <p>© 2025 Bowl of Hygea Pharmacy. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
