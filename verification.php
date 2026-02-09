<?php
// verification.php
session_start();
require_once __DIR__ . "/db/hygeadb.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Accept AJAX POST to store verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
  $supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
  $cartKey = 'supplier_' . $supplier_id;
  $cart = $_SESSION['po_cart'][$cartKey] ?? [];

  if (empty($cart)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No items to send.']);
    exit;
  }

  // Ensure table exists
  $sqlCreate = "CREATE TABLE IF NOT EXISTS po_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    branch_id INT DEFAULT NULL,
    data TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
  $conn->query($sqlCreate);

  $dataJson = json_encode(array_values($cart), JSON_UNESCAPED_UNICODE);
  // ensure branch columns exist (non-destructive)
  $conn->query("ALTER TABLE po_verification ADD COLUMN IF NOT EXISTS branch_name VARCHAR(255) DEFAULT NULL");
  $conn->query("ALTER TABLE po_verification ADD COLUMN IF NOT EXISTS branch_manager VARCHAR(255) DEFAULT NULL");

  $branch_name = trim($_POST['branch_name'] ?? '');
  $branch_manager = trim($_POST['branch_manager'] ?? '');

  $stmt = $conn->prepare("INSERT INTO po_verification (supplier_id, branch_name, branch_manager, data, status) VALUES (?, ?, ?, ?, 'pending')");
  if ($stmt) {
    $stmt->bind_param('isss', $supplier_id, $branch_name, $branch_manager, $dataJson);
    $ok = $stmt->execute();
    if ($ok) {
      // clear the cart for this supplier
      $_SESSION['po_cart'][$cartKey] = [];
      header('Content-Type: application/json');
      echo json_encode(['success' => true, 'message' => 'Purchase Order sent for verification.']);
      exit;
    }
  }

  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Failed to save verification request.']);
  exit;
}

// Handle verify / disregard actions (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['verify', 'disregard'])) {
  $action = $_POST['action'];
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
  }

  $newStatus = ($action === 'verify') ? 'verified' : 'disregarded';
  $stmt = $conn->prepare("UPDATE po_verification SET status = ? WHERE id = ?");
  if ($stmt) {
    $stmt->bind_param('si', $newStatus, $id);
    $ok = $stmt->execute();
    header('Content-Type: application/json');
    echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'OK' : $conn->error]);
    exit;
  }
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Failed to update status']);
  exit;
}

// Page: list verification requests (owner view)
$rows = [];
// Exclude disregarded items so they disappear after being disregarded
$res = $conn->query("SELECT id, supplier_id, data, status, created_at, branch_name FROM po_verification WHERE status != 'disregarded' ORDER BY created_at DESC");
if ($res) {
  while ($r = $res->fetch_assoc()) $rows[] = $r;
}

// If requested as modal/partial, return only the inner list fragment
$isPartial = false;
if ((isset($_GET['modal']) && $_GET['modal'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
  $isPartial = true;
}
if ($isPartial) {
  header('Content-Type: text/html; charset=utf-8');
  if (empty($rows)) {
    echo '<div class="p-6 bg-white border rounded">No pending verification requests.</div>';
    exit;
  }
  echo '<div class="space-y-4">';
  foreach ($rows as $r) {
    $sid = (int)$r['supplier_id'];
    $sres = $conn->query("SELECT supplier_name FROM supplier WHERE supplier_id = " . $sid);
    $sname = $sres && $sres->num_rows ? $sres->fetch_assoc()['supplier_name'] : 'Unknown';
    $items = json_decode($r['data'], true);
    $status = $r['status'] ?? 'pending';
    $badgeClass = 'bg-yellow-100 text-yellow-700';
    $statusLabel = 'Pending';
    if ($status === 'verified') { $badgeClass = 'bg-green-100 text-green-700'; $statusLabel = 'Verified'; }
    if ($status === 'disregarded') { $badgeClass = 'bg-red-100 text-red-700'; $statusLabel = 'Disregarded'; }
    $branchName = trim($r['branch_name'] ?? '');
    $branchManager = $r['branch_manager'] ?? '';
    echo '<div class="bg-white border rounded p-4" data-po-id="' . (int)$r['id'] . '">';
    echo '<div class="flex items-start justify-between">';
    echo '<div>';
    // Show which branch submitted the request; fallback to request id
    if ($branchName) {
      echo '<div class="text-sm text-gray-500">Request from ' . h($branchName) . ' • ' . h($r['created_at']) . '</div>';
    } else {
      echo '<div class="text-sm text-gray-500">Request #' . (int)$r['id'] . ' • ' . h($r['created_at']) . '</div>';
    }
    echo '<div class="font-semibold mt-1">Supplier: ' . h($sname) . '</div>';
    if ($branchManager) echo '<div class="text-sm text-gray-600">Branch Manager: ' . h($branchManager) . '</div>';
    echo '</div>';
    // close button + status
    echo '<div class="flex items-start gap-3">';
    echo '<button data-action="close" data-id="' . (int)$r['id'] . '" class="po-close-btn text-gray-400 hover:text-gray-700">✕</button>';
    echo '<span><span class="po-status-badge px-2 py-1 rounded-full text-xs font-semibold ' . $badgeClass . '">' . $statusLabel . '</span></span>';
    echo '</div>';
    echo '</div>';
    echo '<div class="mt-3 text-sm text-gray-700"><ul class="list-disc pl-5">';
    foreach ($items as $it) {
      $label = (isset($it['brand']) && $it['brand'] ? $it['brand'] . ' ' : '') . (isset($it['strength']) && $it['strength'] ? '(' . $it['strength'] . ') ' : '') . (isset($it['generic']) ? $it['generic'] : ($it['brand'] ?? ''));
      echo '<li>' . h($label) . ' — Qty: ' . (int)($it['qty'] ?? 0) . '</li>';
    }
    echo '</ul></div>';
    echo '<div class="mt-3 text-sm text-gray-500">Status: ' . h($r['status']) . '</div>';
    // action buttons
    echo '<div class="mt-3 flex justify-end gap-2">';
    echo '<button data-action="disregard" data-id="' . (int)$r['id'] . '" class="disregard-btn px-3 py-1 rounded bg-red-50 text-red-700 border">Disregard</button>';
    echo '<button data-action="verify" data-id="' . (int)$r['id'] . '" class="verify-btn px-3 py-1 rounded bg-green-50 text-green-700 border">Verified</button>';
    echo '</div>';
    echo '</div>';
  }
  echo '</div>';
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Purchase Order Verification</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = {theme:{extend:{colors:{primary:'#274497', lightgray:'#CDCFD6'}}}};</script>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex">

  <!-- SIDEBAR (copied style) -->
  <aside class="w-64 bg-white border-r min-h-screen flex flex-col">
    <div class="px-6 py-5 flex items-center gap-3 border-b">
      <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
        <span class="text-primary font-bold">◻</span>
      </div>
      <div>
        <h1 class="font-bold text-primary leading-tight">Supplier</h1>
        <p class="text-xs text-gray-500">Module</p>
      </div>
    </div>

    <nav class="px-3 py-4 space-y-1">
      <a href="supplier.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🏠</span> Dashboard
      </a>

      <a href="sales.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>💰</span> Sales
      </a>

      <a href="inventory.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>📊</span> Inventory
      </a>

      <a href="suppliers.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🏭</span> Suppliers
      </a>

      <a href="#" onclick="openVerification(event)" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🔎</span> Verification
      </a>

      <a href="purchase_order.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>📦</span> Purchase Order
      </a>

      <a href="#" onclick="openHistory(event)" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🧾</span> Order History
      </a>
    </nav>

    <div class="mt-auto p-4 relative">
      <button id="profileBtn" class="w-full bg-gray-50 rounded-xl p-3 border flex items-center gap-3 hover:bg-gray-100 transition-colors">
        <div class="w-10 h-10 rounded-full bg-gray-200"></div>
        <div class="text-left">
          <p class="text-sm font-semibold">Admin User</p>
          <p class="text-xs text-gray-500">Main Branch</p>
        </div>
      </button>
      <div id="profileDropdown" class="absolute bottom-16 left-4 right-4 bg-white border rounded-xl shadow-lg overflow-hidden hidden z-50">
        <ul class="divide-y">
          <li><button class="w-full text-left px-4 py-3 text-sm hover:bg-gray-100 transition-colors">Profile</button></li>
          <li><button class="w-full text-left px-4 py-3 text-sm hover:bg-gray-100 transition-colors">Settings</button></li>
          <li><button class="w-full text-left px-4 py-3 text-sm hover:bg-gray-100 transition-colors">Help</button></li>
          <li><button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">Logout</button></li>
        </ul>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="flex-1 flex flex-col">
    <header class="bg-white border-b">
      <div class="px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-gray-500">
          <h2 class="text-lg font-semibold">Purchase Order Verification</h2>
        </div>
        <div class="flex items-center gap-3">
          <button class="p-2 rounded-lg hover:bg-gray-100" title="Back to Dashboard" onclick="window.location.href='dashboard.php'">←</button>
          <button class="p-2 rounded-lg hover:bg-gray-100" title="Notifications">🔔</button>
        </div>
      </div>
    </header>

    <main class="px-10 py-10">
      <div class="max-w-4xl mx-auto">
        <?php if (empty($rows)): ?>
          <div class="p-6 bg-white border rounded">No pending verification requests.</div>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($rows as $r): ?>
              <div class="bg-white border rounded p-4">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="text-sm text-gray-500">Request #<?php echo (int)$r['id']; ?> • <?php echo h($r['created_at']); ?></div>
                    <div class="font-semibold mt-1">Supplier: <?php
                      $sid = (int)$r['supplier_id'];
                      $sres = $conn->query("SELECT supplier_name FROM supplier WHERE supplier_id = " . $sid);
                      $sname = $sres && $sres->num_rows ? $sres->fetch_assoc()['supplier_name'] : 'Unknown';
                      echo h($sname);
                    ?></div>
                  </div>
                </div>
                <div class="mt-3 text-sm text-gray-700">
                  <?php $items = json_decode($r['data'], true); ?>
                  <ul class="list-disc pl-5">
                    <?php foreach ($items as $it): ?>
                      <li><?php echo h((($it['brand'] ?? '') ? $it['brand'] . ' ' : '') . (($it['strength'] ?? '') ? '(' . $it['strength'] . ') ' : '') . ($it['generic'] ?? $it['brand'])); ?> — Qty: <?php echo (int)($it['qty'] ?? 0); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div class="mt-3 text-sm text-gray-500">Status: <?php echo h($r['status']); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<script>
  // Profile dropdown toggle
  const profileBtn = document.getElementById("profileBtn");
  const profileDropdown = document.getElementById("profileDropdown");

  if (profileBtn) profileBtn.addEventListener("click", () => profileDropdown.classList.toggle("hidden"));

  document.addEventListener("click", (e) => {
    if (profileBtn && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.classList.add("hidden");
    }
  });

  // Open verification when requested from modal-based links
  function openVerification(e) {
    if (e) e.preventDefault();
    window.location = 'verification.php';
  }
</script>
</body>
</html>
