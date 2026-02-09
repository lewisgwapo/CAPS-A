<?php
// supplier.php
session_start();

// DATABASE CONNECTION
// include __DIR__ . "/db/db.php";
$conn = null;

// Mock supplier data (alphabetical)
$suppliers = [
    ['supplier_id' => 1, 'supplier_name' => 'Global Health Pharma', 'contact_number' => '+1-555-0103', 'status' => 'Inactive'],
    ['supplier_id' => 2, 'supplier_name' => 'MediSupply Inc', 'contact_number' => '+1-555-0102', 'status' => 'Active'],
    ['supplier_id' => 1, 'supplier_name' => 'PharmaCorp Ltd', 'contact_number' => '+1-555-0101', 'status' => 'Active'],
];

// Sort alphabetically
usort($suppliers, fn($a, $b) => strcasecmp($a['supplier_name'], $b['supplier_name']));

// quick counts (for top cards)
$totalSuppliers = count($suppliers);
$totalOrders = 0;      // placeholder (connect later)
$pendingItems = 0;     // placeholder (connect later)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Supplier | Dashboard</title>

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

<body class="bg-gray-50 text-gray-800">

<div class="min-h-screen flex">

  <!-- SIDEBAR -->
  <aside class="w-64 bg-white border-r min-h-screen flex flex-col">
    <div class="px-6 py-5 flex items-center gap-3 border-b">
      <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
        <span class="text-primary font-bold">◻</span>
      </div>
      <div>
        <!-- Change Inventory -> Supplier -->
        <h1 class="font-bold text-primary leading-tight">Supplier</h1>
        <p class="text-xs text-gray-500">Module</p>
      </div>
    </div>

    <nav class="px-3 py-4 space-y-1">

  <a href="supplier.php"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo (basename($_SERVER['PHP_SELF']) === 'supplier.php' && empty($_GET)) ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
    <span>🏠</span> Dashboard
  </a>

  <a href="sales.php"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo (basename($_SERVER['PHP_SELF']) === 'sales.php') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
    <span>💰</span> Sales
  </a>

  <a href="inventory.php"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo (basename($_SERVER['PHP_SELF']) === 'inventory.php') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
    <span>📊</span> Inventory
  </a>

  <a href="suppliers.php"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?php echo (basename($_SERVER['PHP_SELF']) === 'suppliers.php') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
    <span>🏭</span> Suppliers
  </a>

  <a href="#" onclick="openVerification(event)"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
    <span>🔎</span> Verification
  </a>

  <!-- NEW PURCHASE ORDER BUTTON -->
  <a href="purchase_order.php"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
    <span>📦</span> Purchase Order
  </a>

  <a href="#" onclick="openHistory(event)"
     class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
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

        <!-- Search (with suggestions dropdown) -->
        <div class="relative w-full max-w-xl">
          <div class="flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2">
            <span class="text-gray-500">🔎</span>
            <input
              id="supplierSearch"
              type="text"
              placeholder="Search..."
              class="bg-transparent w-full outline-none text-sm"
              autocomplete="off"
            />
          </div>

          <!-- Suggestions Dropdown -->
          <div
            id="suggestionsBox"
            class="absolute left-0 right-0 mt-2 bg-white border rounded-xl shadow-lg overflow-hidden hidden z-50"
          >
            <ul id="suggestionsList" class="divide-y"></ul>
          </div>
        </div>

        <!-- Right icons -->
        <div class="flex items-center gap-3">
          <button
            class="p-2 rounded-lg hover:bg-gray-100"
            title="Back to Dashboard"
            onclick="window.location.href='dashboard.php'">
            ←
        </button>

          <button class="p-2 rounded-lg hover:bg-gray-100" title="Notifications">🔔</button>
        </div>
      </div>
    </header>

    <!-- CONTENT -->
    <main class="px-10 py-10">
      <h2 class="text-3xl font-bold">Dashboard</h2>
      <p class="text-gray-500 mt-1">Overview of your supplier operations.</p>

      <!-- Top cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white border rounded-2xl p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">🚚</div>
          <div>
            <p class="text-sm text-gray-500">Total Suppliers</p>
            <p class="text-2xl font-bold"><?php echo $totalSuppliers; ?></p>
          </div>
        </div>

        <div class="bg-white border rounded-2xl p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">📦</div>
          <div>
            <p class="text-sm text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold"><?php echo $totalOrders; ?></p>
          </div>
        </div>

        <div class="bg-white border rounded-2xl p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">🛒</div>
          <div>
            <p class="text-sm text-gray-500">Pending Items</p>
            <p class="text-2xl font-bold"><?php echo $pendingItems; ?></p>
          </div>
        </div>
      </div>

      <!-- Big CTA -->
      <section class="mt-10">
        <div class="bg-gradient-to-r from-primary to-blue-600 rounded-2xl p-8 text-white shadow-lg hover:shadow-2xl hover:shadow-primary/30 transition-all duration-300">
          <h3 class="text-2xl font-bold">Ready to restock?</h3>
          <p class="text-white/90 mt-2 max-w-xl">
            Start a new purchase order by selecting a supplier from your verified list.
          </p>
          <div class="mt-6">
            <a href="suppliers.php"
               class="inline-flex items-center gap-2 bg-white text-primary px-5 py-3 rounded-xl font-semibold hover:bg-primary hover:text-white hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
              Select Supplier <span class="group-hover:translate-x-1 transition-transform">→</span>
            </a>
          </div>
        </div>
      </section>

      <!-- (Optional) Supplier table preview -->
      <section class="mt-10">
        <div class="bg-white border rounded-2xl overflow-hidden">
          <div class="px-6 py-4 flex items-center justify-between">
            <h4 class="font-bold">Supplier List (Preview)</h4>
            <a class="text-sm text-primary hover:underline" href="suppliers.php">Go to Suppliers</a>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left px-6 py-3 font-semibold text-gray-600">Supplier</th>
                  <th class="text-left px-6 py-3 font-semibold text-gray-600">Contact</th>
                  <th class="text-left px-6 py-3 font-semibold text-gray-600">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php if (count($suppliers) === 0): ?>
                  <tr>
                    <td class="px-6 py-6 text-gray-500" colspan="3">No suppliers found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach (array_slice($suppliers, 0, 5) as $s): ?>
                    <tr>
                      <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($s['supplier_name']); ?></td>
                      <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($s['contact_number'] ?? '—'); ?></td>
                      <td class="px-6 py-4">
                        <?php
                          $status = strtolower($s['status'] ?? 'active');
                          $isActive = ($status === 'active');
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                          <?php echo $isActive ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'; ?>">
                          <?php echo $isActive ? 'ACTIVE' : 'INACTIVE'; ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>
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

  // Verification modal loader
  function openVerification(e) {
    if (e) e.preventDefault();
    const modal = document.getElementById('verificationModal');
    const content = document.getElementById('verificationModalContent');
    if (!modal || !content) {
      // fallback: navigate
      window.location = 'verification.php';
      return;
    }
    content.innerHTML = '<div class="p-6 text-center text-gray-500">Loading...</div>';
    modal.classList.remove('hidden');

    fetch('verification.php?modal=1')
      .then(r => r.text())
      .then(html => { content.innerHTML = html; })
      .catch(() => { content.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load verification.</div>'; });
  }

  document.getElementById('verificationModal')?.addEventListener('click', (ev) => {
    if (ev.target.id === 'verificationModal') document.getElementById('verificationModal').classList.add('hidden');
  });
  document.getElementById('closeVerificationBtn')?.addEventListener('click', () => {
    document.getElementById('verificationModal').classList.add('hidden');
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
  document.getElementById('closeVerificationBtn')?.addEventListener('click', () => {
    document.getElementById('verificationModal').classList.add('hidden');
  });

  // Supplier names for suggestions (alphabetical already from PHP)
  const supplierData = <?php
    // Only send needed fields to JS
    $js = [];
    foreach ($suppliers as $s) {
      $js[] = [
        "id" => $s["supplier_id"],
        "name" => $s["supplier_name"],
      ];
    }
    echo json_encode($js);
  ?>;

  const input = document.getElementById("supplierSearch");
  const box = document.getElementById("suggestionsBox");
  const list = document.getElementById("suggestionsList");

  function openBox() {
    box.classList.remove("hidden");
  }

  function closeBox() {
    box.classList.add("hidden");
  }

  function clearList() {
    list.innerHTML = "";
  }

  // Filter function:
  // - matches STARTS WITH or CONTAINS
  // - stays on same page (no redirect)
  function getMatches(q) {
    const query = q.trim().toLowerCase();
    if (!query) return [];

    // startsWith first, then contains (both alphabetical)
    const starts = supplierData.filter(s => s.name.toLowerCase().startsWith(query));
    const contains = supplierData.filter(s =>
      !s.name.toLowerCase().startsWith(query) && s.name.toLowerCase().includes(query)
    );

    // supplierData already alphabetical from PHP, so order is stable
    return [...starts, ...contains].slice(0, 8); // limit suggestions
  }

  function renderSuggestions(matches) {
    clearList();
    if (matches.length === 0) {
      list.innerHTML = `<li class="px-4 py-3 text-sm text-gray-500">No matches</li>`;
      openBox();
      return;
    }

    matches.forEach(item => {
      const li = document.createElement("li");
      li.className = "px-4 py-3 text-sm hover:bg-gray-100 cursor-pointer";
      li.textContent = item.name;

      li.addEventListener("click", () => {
        input.value = item.name;   // keep selected text
        closeBox();
      });

      list.appendChild(li);
    });

    openBox();
  }

  input.addEventListener("input", () => {
    const matches = getMatches(input.value);
    if (input.value.trim() === "") {
      closeBox();
      return;
    }
    renderSuggestions(matches);
  });

  // Close dropdown when clicking outside
  document.addEventListener("click", (e) => {
    if (!box.contains(e.target) && e.target !== input) closeBox();
  });

  // Escape closes suggestions
  input.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeBox();
  });

  // Verification modal HTML appended to this page
</script>

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

</body>
</html>
