<?php
// suppliers.php
session_start();

// DATABASE CONNECTION
require_once __DIR__ . "/db/hygeadb.php";

// Handle adding supplier via database
$flash = "";
$flashType = "success";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'edit_supplier') {
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($supplier_id > 0 && $supplier_name !== '') {
      $stmt = $conn->prepare("UPDATE supplier SET supplier_name = ?, contact_name = ?, contact_number = ? WHERE supplier_id = ?");
      $stmt->bind_param("sssi", $supplier_name, $contact_name, $contact_number, $supplier_id);
      
      if ($stmt->execute()) {
        $flash = "✅ Supplier updated successfully.";
        $flashType = "success";
      } else {
        $flash = "Error updating supplier: " . $conn->error;
        $flashType = "error";
      }
      $stmt->close();
    } else {
      $flash = "Supplier name is required.";
      $flashType = "error";
    }
  }
  
  if ($_POST['action'] === 'add_supplier') {
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($supplier_name === '') {
      $flash = "Supplier name is required.";
      $flashType = "error";
    } else {
      // Insert into database
      $stmt = $conn->prepare("INSERT INTO supplier (supplier_name, contact_name, contact_number) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $supplier_name, $contact_name, $contact_number);
      
      if ($stmt->execute()) {
        $flash = "✅ Supplier added successfully.";
        $flashType = "success";
      } else {
        $flash = "Error adding supplier: " . $conn->error;
        $flashType = "error";
      }
      $stmt->close();
    }
  }
  
  // Delete supplier
  if ($_POST['action'] === 'delete_supplier') {
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    
    if ($supplier_id > 0) {
      $stmt = $conn->prepare("DELETE FROM supplier WHERE supplier_id = ?");
      $stmt->bind_param("i", $supplier_id);
      
      if ($stmt->execute()) {
        $flash = "✅ Supplier deleted successfully.";
        $flashType = "success";
      } else {
        // Check if it's a foreign key constraint error
        if ($conn->errno == 1451 || strpos($conn->error, 'foreign key constraint') !== false) {
          $flash = "Cannot delete this supplier because it has associated medicine batches. Please remove the medicine batches first.";
          $flashType = "error";
        } else {
          $flash = "Error deleting supplier: " . $conn->error;
          $flashType = "error";
        }
      }
      $stmt->close();
    }
  }
}

// Fetch suppliers from database
$suppliers = [];
$result = $conn->query("SELECT supplier_id, supplier_name, contact_name, contact_number FROM supplier ORDER BY supplier_name");

if ($result) {
  while ($row = $result->fetch_assoc()) {
    // Add status to match the original structure
    $row['status'] = 'Active';
    $suppliers[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Select Supplier</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { primary: '#274497' }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-50 text-gray-800">
<div class="min-h-screen flex">

  <!-- SIDEBAR -->
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
      <a href="supplier.php"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🏠</span> Dashboard
      </a>

      <a href="sales.php"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>💰</span> Sales
      </a>

      <a href="inventory.php"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>📊</span> Inventory
      </a>

      <a href="suppliers.php"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm bg-primary text-white">
        <span>🏭</span> Suppliers
      </a>

      <a href="purchase_order.php"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>📦</span> Purchase Order
      </a>

      <a href="#" onclick="openHistory(event)"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🧾</span> Order History
      </a>

      <a href="#" onclick="openVerification(event)"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🔎</span> Verification
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
      
      <!-- Profile Dropdown Menu -->
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

    <!-- TOP BAR -->
    <header class="bg-white border-b">
      <div class="px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-gray-500">
          <span>🔎</span>
          <input class="w-[420px] max-w-full text-sm outline-none"
                 placeholder="Search..." />
        </div>
        <div class="flex items-center gap-3">
          <button class="p-2 rounded-lg hover:bg-gray-100" title="Notifications">🔔</button>
          <button id="addSupplierBtn" class="ml-2 bg-primary text-white px-4 py-2 rounded-lg font-semibold shadow">Add Supplier</button>
        </div>
      </div>
    </header>

    <main class="px-10 py-10">
      <!-- Header -->
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-3xl font-bold">Select Supplier</h2>
          <p class="text-gray-500 mt-1">Choose a supplier to start a purchase order.</p>
        </div>

        <a href="#" onclick="openHistory(event)" class="text-sm text-gray-600 hover:text-primary flex items-center gap-2">
          View Order History <span>→</span>
        </a>
      </div>

      <?php if ($flash): ?>
        <div class="mt-6 px-4 py-3 rounded-xl border text-sm
          <?php echo $flashType === 'error'
            ? 'bg-red-50 border-red-200 text-red-700'
            : 'bg-green-50 border-green-200 text-green-700'; ?>">
          <?php echo h($flash); ?>
        </div>
      <?php endif; ?>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">

        <?php if (count($suppliers) === 0): ?>
          <div class="bg-white border rounded-2xl p-6 text-gray-600">
            No suppliers found.
          </div>
        <?php endif; ?>

        <?php foreach ($suppliers as $s): ?>
          <?php
            $name = $s['supplier_name'];
            $person = $s['contact_name'] ?? '—';
            $phone = $s['contact_number'] ?? '—';
            $status = strtolower($s['status'] ?? 'active');
            $isVerified = ($status === 'active'); // simple rule
          ?>

          <!-- Card container with delete button -->
          <div class="relative bg-white border border-gray-200 rounded-2xl p-6 hover:border-primary hover:shadow-xl hover:shadow-primary/10 transition-all duration-300 group">
            <!-- Action buttons (bottom right) -->
            <div class="absolute bottom-4 right-4 flex gap-2">
              <!-- Edit button -->
              <button onclick="openEditModal(<?= (int)$s['supplier_id'] ?>, '<?= htmlspecialchars($s['supplier_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['contact_name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($s['contact_number'] ?? '', ENT_QUOTES) ?>')" 
                      class="p-2 rounded-lg text-blue-500 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300" title="Edit supplier">
                ✏️
              </button>
              <!-- Delete button -->
              <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                <input type="hidden" name="action" value="delete_supplier">
                <input type="hidden" name="supplier_id" value="<?= (int)$s['supplier_id'] ?>">
                <button type="submit" class="p-2 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-300" title="Delete supplier">
                  🗑️
                </button>
              </form>
            </div>

            <!-- Click card -> goes to purchase_order.php with supplier_id -->
            <a href="purchase_order.php?supplier_id=<?= (int)$s['supplier_id'] ?>"
               class="block">

              <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-primary/10 group-hover:bg-primary/20 flex items-center justify-center transition-colors duration-300">
                  🚚
                </div>

                <span class="text-xs px-3 py-1 rounded-full group-hover:bg-primary group-hover:text-white transition-all duration-300
                  <?= $isVerified ? 'bg-gray-100 text-gray-700' : 'bg-gray-200 text-gray-600' ?>">
                  <?= $isVerified ? 'Verified' : 'Inactive' ?>
                </span>
              </div>

              <h3 class="mt-6 font-bold text-lg group-hover:text-primary transition-colors duration-300">
                <?= htmlspecialchars($name) ?>
              </h3>

              <div class="mt-4 text-sm text-gray-600 space-y-2">
                <div class="flex items-center gap-2">
                  <span>👤</span>
                  <span><?= htmlspecialchars($person) ?></span>
                </div>
                <div class="flex items-center gap-2">
                  <span>📞</span>
                  <span><?= htmlspecialchars($phone) ?></span>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>

      </div>
    </main>

  </div>
</div>

<!-- Add Supplier Modal -->
<div id="addSupplierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4">
    <h3 class="text-xl font-bold text-primary mb-4">Add Supplier</h3>
    <form method="POST">
      <input type="hidden" name="action" value="add_supplier">
      <div class="grid grid-cols-1 gap-3">
        <div>
          <label class="text-sm font-medium">Supplier Name *</label>
          <input name="supplier_name" type="text" class="w-full px-3 py-2 border rounded" required />
        </div>
        <div>
          <label class="text-sm font-medium">Contact Name</label>
          <input name="contact_name" type="text" class="w-full px-3 py-2 border rounded" />
        </div>
        <div>
          <label class="text-sm font-medium">Contact Number</label>
          <input name="contact_number" type="text" class="w-full px-3 py-2 border rounded" />
        </div>
      </div>
      <div class="mt-4 flex gap-3">
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded font-semibold">Save Supplier</button>
        <button type="button" id="closeAddSupplier" class="px-4 py-2 rounded border">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Profile dropdown toggle
  const profileBtn = document.getElementById("profileBtn");
  const profileDropdown = document.getElementById("profileDropdown");

  profileBtn.addEventListener("click", () => {
    profileDropdown.classList.toggle("hidden");
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
      profileDropdown.classList.add("hidden");
    }
  });

  // Add Supplier modal
  const addSupplierBtn = document.getElementById('addSupplierBtn');
  const addSupplierModal = document.getElementById('addSupplierModal');
  const closeAddSupplier = document.getElementById('closeAddSupplier');

  addSupplierBtn.addEventListener('click', () => addSupplierModal.classList.remove('hidden'));
  closeAddSupplier.addEventListener('click', () => addSupplierModal.classList.add('hidden'));
  addSupplierModal.addEventListener('click', (e) => { if (e.target === addSupplierModal) addSupplierModal.classList.add('hidden'); });
</script>

  <!-- Edit Supplier Modal -->
  <div id="editSupplierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4">
      <h3 class="text-xl font-bold text-primary mb-4">Edit Supplier</h3>
      <form method="POST">
        <input type="hidden" name="action" value="edit_supplier">
        <input type="hidden" name="supplier_id" id="editSupplierId" />
        <div class="grid grid-cols-1 gap-3">
          <div>
            <label class="text-sm font-medium">Supplier Name *</label>
            <input name="supplier_name" id="editSupplierName" type="text" class="w-full px-3 py-2 border rounded" required />
          </div>
          <div>
            <label class="text-sm font-medium">Contact Name</label>
            <input name="contact_name" id="editContactName" type="text" class="w-full px-3 py-2 border rounded" />
          </div>
          <div>
            <label class="text-sm font-medium">Contact Number</label>
            <input name="contact_number" id="editContactNumber" type="text" class="w-full px-3 py-2 border rounded" />
          </div>
        </div>
        <div class="mt-4 flex gap-3">
          <button type="submit" class="bg-primary text-white px-4 py-2 rounded font-semibold">Save Changes</button>
          <button type="button" id="closeEditSupplier" class="px-4 py-2 rounded border">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- History modal (loads history_po.php into modal) -->
  <div id="historyModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-start justify-center p-8">
    <div id="historyModalBox" class="bg-white rounded-xl shadow-lg max-w-5xl w-full max-h-[90vh] overflow-auto">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="font-semibold">Order History</h3>
        <button id="closeHistoryBtn" class="text-gray-600 hover:text-gray-800">✕</button>
      </div>
      <div id="historyModalContent" class="p-6"></div>
    </div>
  </div>

  <!-- Verification modal (loads verification.php into modal) -->
  <div id="verificationModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-start justify-center p-8">
    <div id="verificationModalBox" class="bg-white rounded-xl shadow-lg max-w-5xl w-full max-h-[90vh] overflow-auto">
      <div class="flex items-center justify-between px-6 py-4 border-b">
        <h3 class="font-semibold">Verification Requests</h3>
        <button id="closeVerificationBtn" class="text-gray-600 hover:text-gray-800">✕</button>
      </div>
      <div id="verificationModalContent" class="p-6"></div>
    </div>
  </div>

  <script>
    function openEditModal(id, name, contactName, contactNumber) {
      document.getElementById('editSupplierId').value = id;
      document.getElementById('editSupplierName').value = name;
      document.getElementById('editContactName').value = contactName;
      document.getElementById('editContactNumber').value = contactNumber;
      document.getElementById('editSupplierModal').classList.remove('hidden');
    }

    document.getElementById('closeEditSupplier').addEventListener('click', () => {
      document.getElementById('editSupplierModal').classList.add('hidden');
    });

    document.getElementById('editSupplierModal').addEventListener('click', (e) => {
      if (e.target.id === 'editSupplierModal') {
        document.getElementById('editSupplierModal').classList.add('hidden');
      }
    });

    function openHistory(e) {
      if (e) e.preventDefault();
      const modal = document.getElementById('historyModal');
      const content = document.getElementById('historyModalContent');
      content.innerHTML = '<div class="p-6 text-center text-gray-500">Loading...</div>';
      modal.classList.remove('hidden');

      fetch('history_po.php')
        .then(r => r.text())
        .then(html => {
          try {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const fragment = doc.querySelector('div.p-8');
            content.innerHTML = (fragment && fragment.innerHTML) ? fragment.innerHTML : html;
          } catch (err) {
            content.innerHTML = html;
          }
        })
        .catch(() => {
          content.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load history.</div>';
        });
    }

    document.getElementById('closeHistoryBtn').addEventListener('click', () => {
      document.getElementById('historyModal').classList.add('hidden');
    });

    document.getElementById('historyModal').addEventListener('click', (ev) => {
      if (ev.target.id === 'historyModal') document.getElementById('historyModal').classList.add('hidden');
    });
    
    // Verification modal loader
    function openVerification(e) {
      if (e) e.preventDefault();
      const modal = document.getElementById('verificationModal');
      const content = document.getElementById('verificationModalContent');
      content.innerHTML = '<div class="p-6 text-center text-gray-500">Loading...</div>';
      modal.classList.remove('hidden');

      fetch('verification.php?modal=1')
        .then(r => r.text())
        .then(html => { content.innerHTML = html; })
        .catch(() => { content.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load verification.</div>'; });
    }

    document.getElementById('closeVerificationBtn')?.addEventListener('click', () => {
      document.getElementById('verificationModal').classList.add('hidden');
    });

    document.getElementById('verificationModal')?.addEventListener('click', (ev) => {
      if (ev.target.id === 'verificationModal') document.getElementById('verificationModal').classList.add('hidden');
    });
    // Delegated handler for verification actions inside modal
    (function(){
      const content = document.getElementById('verificationModalContent');
      if (!content) return;
      content.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (action === 'close') { const card = btn.closest('[data-po-id]'); if (card) card.remove(); return; }
        btn.disabled = true;
        try {
          const fd = new FormData();
          fd.append('action', action);
          fd.append('id', id);
          const res = await fetch('verification.php', { method: 'POST', body: fd, credentials: 'same-origin' });
          const data = await res.json();
          if (data.success) {
            const card = btn.closest('[data-po-id]');
            if (action === 'disregard') { if (card) card.remove(); }
            else if (action === 'verify') { if (card) { const badge = card.querySelector('.po-status-badge'); if (badge) { badge.className = 'po-status-badge px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700'; badge.textContent = 'Verified'; } card.querySelectorAll('button[data-action]').forEach(b=>b.disabled=true); } }
          } else { alert(data.message || 'Failed'); btn.disabled = false; }
        } catch (err) { alert('Network error'); btn.disabled = false; }
      });
    })();
  </script>
  </body>
  </html>
