<?php
session_start();
require_once __DIR__ . "/db/hygeadb.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = "";

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username and password are required.";
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role_id, branch_id
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);

            if (!$stmt->execute()) {
                $error = "Query error: " . $stmt->error;
            } else {
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {

                    $user = $result->fetch_assoc();
                    $stored = $user['password'];

                    // Password verification (hashed OR plaintext)
                    $password_ok = false;
                    if (password_verify($password, $stored)) {
                        $password_ok = true;
                    } elseif ($password === $stored) {
                        $password_ok = true;

                        // Optional rehash plaintext -> hashed
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $up = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        if ($up) {
                            $up->bind_param("si", $newHash, $user['user_id']);
                            $up->execute();
                            $up->close();
                        }
                    }

                    if ($password_ok) {
                        $_SESSION['user_id']   = $user['user_id'];
                        $_SESSION['username']  = $user['username'];
                        $_SESSION['role_id']   = $user['role_id'];
                        $_SESSION['branch_id'] = $user['branch_id'];

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Incorrect password.";
                    }

                } else {
                    $error = "User not found.";
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bowl of Hygea Pharmacy - Login</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind custom colors -->
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

<body class="min-h-screen flex items-center justify-center bg-lightbg font-sans">

    <div class="w-full max-w-md bg-white rounded-xl shadow-xl overflow-hidden">

        <!-- Header -->
        <div class="bg-primary text-white text-center py-8">
            <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-white flex items-center justify-center">
                <!-- Check Icon -->
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-xl font-semibold">Bowl of Hygea Pharmacy</h1>
            <p class="text-sm opacity-90">Sales & Inventory Management System</p>
        </div>

        <!-- Body -->
        <div class="p-8">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Welcome Back</h2>
            <p class="text-gray-500 text-sm text-center mb-6">
                Please sign in to your account
            </p>

            <!-- ✅ ONLY CHANGE HERE: validateForm(this) instead of validateForm(event) -->
            <form method="POST"
                  action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                  class="space-y-5"
                  novalidate
                  onsubmit="return validateForm(this)">

                <?php if (!empty($error)): ?>
                    <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Username -->
                <div>
                    <label class="text-sm text-gray-700">Username</label>
                    <div class="relative mt-1">
                        <input type="text" id="username" name="username"
                               placeholder="Enter your username"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="text-sm text-gray-700">Password</label>
                    <div class="relative mt-1">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-primary">
                             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                             d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                             <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                            c4.478 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.064 7-9.542 7
                            -4.477 0-8.268-2.943-9.542-7z" />
                         </svg>
                        </button>

                    </div>
                </div>

                <!-- Remember / Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" class="rounded">
                        Remember me
                    </label>
                    <a href="#" onclick="showForgotPasswordModal(event)" class="text-primary hover:underline">
                        Forgot password?
                    </a>
                </div>

                <!-- Button -->
                <button type="submit"
                        class="w-full bg-primary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                    Sign In
                </button>
            </form>

            <hr class="my-6">

            <div class="text-center text-sm text-gray-500">
                Need help accessing your account?
            </div>

            <div class="flex justify-center mt-3">
                <a href="#" onclick="showSystemStatusModal(event)"
                    class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-100 inline-block">
                        System Status
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <p class="absolute bottom-5 text-xs text-gray-500 text-center">
        © 2025 Bowl of Hygea Pharmacy. All rights reserved.<br>
        Secure pharmacy management system
    </p>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="bg-primary text-white px-6 py-4 flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 class="text-lg font-semibold">Password Reset</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">
                    To reset your password, please contact the <strong>Branch Manager</strong> or the <strong>System Owner</strong>.
                </p>
                <p class="text-gray-600 text-sm mb-6">
                    They will guide you through the verification process and help you set a new password securely.
                </p>
                <div class="bg-lightbg p-4 rounded-lg mb-6">
                    <p class="text-xs text-gray-700"><strong>📞 Contact Information:</strong></p>
                    <p class="text-xs text-gray-600 mt-2">Please reach out to your Branch Manager during business hours.</p>
                </div>
            </div>
            <div class="flex gap-3 p-6 border-t">
                <button onclick="closeForgotPasswordModal()" class="flex-1 bg-primary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                    Got It
                </button>
            </div>
        </div>
    </div>

    <!-- System Status Modal -->
    <div id="systemStatusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="bg-primary text-white px-6 py-4 flex items-center justify-between sticky top-0">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold">System Status</h3>
                </div>
                <button onclick="closeSystemStatusModal()" class="text-white hover:opacity-75">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <!-- Server Status -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-700 font-semibold">🖥️ Server Status</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Operational
                        </span>
                    </div>
                </div>

                <!-- Database Status -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-700 font-semibold">💾 Database Status</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Connected
                        </span>
                    </div>
                </div>

                <!-- System Performance -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-700 font-semibold">📊 System Load</span>
                        <span class="text-gray-600 text-sm">45%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: 45%"></div>
                    </div>
                </div>

                <!-- Last Updated -->
                <div class="text-center pt-4 border-t">
                    <p class="text-xs text-gray-500">
                        Last updated: <span id="lastUpdate"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Validation Alert Modal -->
    <div id="validationAlertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4">
            <div class="bg-red-500 text-white px-6 py-4 flex items-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <h3 class="text-lg font-semibold">Validation Error</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-700" id="validationMessage">
                    Please fill out all required fields.
                </p>
            </div>
            <div class="flex gap-3 p-6 border-t">
                <button onclick="closeValidationAlert()" class="w-full bg-primary text-white py-2 rounded-lg font-semibold hover:opacity-90 transition">
                    Understood
                </button>
            </div>
        </div>
    </div>

    <!-- Password Toggle & Modal Scripts -->
    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            input.type = input.type === "password" ? "text" : "password";
        }

        function showForgotPasswordModal(event) {
            event.preventDefault();
            document.getElementById("forgotPasswordModal").classList.remove("hidden");
        }

        function closeForgotPasswordModal() {
            document.getElementById("forgotPasswordModal").classList.add("hidden");
        }

        function showSystemStatusModal(event) {
            event.preventDefault();
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById("lastUpdate").textContent = timeString;
            document.getElementById("systemStatusModal").classList.remove("hidden");
        }

        function closeSystemStatusModal() {
            document.getElementById("systemStatusModal").classList.add("hidden");
        }

        function showValidationAlert(fieldName) {
            let message = "";
            if (fieldName === "username") {
                message = "Please enter your username to proceed.";
            } else if (fieldName === "password") {
                message = "Please enter your password to proceed.";
            } else {
                message = "Please fill out all required fields.";
            }
            document.getElementById("validationMessage").textContent = message;
            document.getElementById("validationAlertModal").classList.remove("hidden");
        }

        function closeValidationAlert() {
            document.getElementById("validationAlertModal").classList.add("hidden");
        }

        // ✅ ONLY CHANGE HERE: validateForm(form) no longer needs event
        function validateForm(form) {
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value.trim();

            console.log("Form submitted - Username:", username, "Password length:", password.length);

            if (!username) {
                showValidationAlert("username");
                return false; // stop submit
            }

            if (!password) {
                showValidationAlert("password");
                return false; // stop submit
            }

            console.log("Validation passed, submitting form...");
            return true; // allow submit
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const forgotModal = document.getElementById("forgotPasswordModal");
            const statusModal = document.getElementById("systemStatusModal");
            const alertModal = document.getElementById("validationAlertModal");

            if (event.target === forgotModal) {
                closeForgotPasswordModal();
            }
            if (event.target === statusModal) {
                closeSystemStatusModal();
            }
            if (event.target === alertModal) {
                closeValidationAlert();
            }
        }
    </script>

</body>
</html>
