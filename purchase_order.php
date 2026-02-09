<?php
// purchase_order.php
session_start();

// DATABASE CONNECTION
require_once __DIR__ . "/db/hygeadb.php";

// Supplier details from URL (passed from suppliers.php)
$branchName = "Main Branch";
$branchNote = "1602 Eusebio Avenue, Pinagbuhatan Pasig City";

// Get suppliers from database
$allSuppliers = [];
$result = $conn->query("SELECT supplier_id, supplier_name, contact_number FROM supplier ORDER BY supplier_name");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $allSuppliers[] = $row;
  }
}

// Determine selected supplier (GET supplier_id > session > default first supplier)
$selectedSupplier = null;
if (isset($_GET['supplier_id'])) {
  $supplier_id = (int)$_GET['supplier_id'];
  foreach ($allSuppliers as $s) {
    if ($s['supplier_id'] == $supplier_id) {
      $selectedSupplier = $s;
      $_SESSION['selected_supplier_id'] = $supplier_id;
      break;
    }
  }
}
elseif (isset($_SESSION['selected_supplier_id'])) {
  $supplier_id = (int)$_SESSION['selected_supplier_id'];
  foreach ($allSuppliers as $s) {
    if ($s['supplier_id'] == $supplier_id) {
      $selectedSupplier = $s;
      break;
    }
  }
}

// Default to first supplier if none selected
if ($selectedSupplier === null && count($allSuppliers) > 0) {
  $selectedSupplier = $allSuppliers[0];
  $_SESSION['selected_supplier_id'] = $selectedSupplier['supplier_id'];
}

// Fallback if no suppliers in database
if ($selectedSupplier === null) {
  $supplierName = "No Supplier Selected";
  $contactPerson = "—";
  $contactPhone = "—";
  $selectedSupplierId = 0;
} else {
  $supplierName = $selectedSupplier['supplier_name'];
  $contactPerson = "—";
  $contactPhone = $selectedSupplier['contact_number'] ?? "—";
  $selectedSupplierId = $selectedSupplier['supplier_id'];
}

// Cart key per supplier (using supplier_id)
if (!isset($_SESSION['po_cart'])) $_SESSION['po_cart'] = [];
$cartKey = 'supplier_' . $selectedSupplierId;
if (!isset($_SESSION['po_cart'][$cartKey])) $_SESSION['po_cart'][$cartKey] = [];

$cart = &$_SESSION['po_cart'][$cartKey];

$flash = "";
$flashType = "success";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Handle actions
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // Add item (use product fields instead of barcode)
  if ($action === 'add_item') {
    $item_type = trim($_POST['item_type'] ?? 'medicine');
    $generic = trim($_POST['generic_name'] ?? '');
    $brand = trim($_POST['brand_name'] ?? '');
    $dosage = trim($_POST['dosage_form'] ?? '');
    $strength = trim($_POST['strength'] ?? '');
    // Product-specific fields
    $product_name = trim($_POST['product_name'] ?? '');
    $unit = trim($_POST['unit'] ?? '');

    // If item is product, map fields accordingly
    if ($item_type === 'product') {
      // Use product name as brand if brand not provided
      if ($brand === '') $brand = $product_name;
      // store unit in dosage field to keep cart structure
      if ($dosage === '') $dosage = $unit;
      // generic left empty for products
      $generic = '';
    }
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    if ($generic === '' && $brand === '') {
      $flash = "Please enter at least a Generic or Brand name.";
      $flashType = "error";
    } else {
      // Generate a stable key for this product
      $keySource = strtolower($generic . '|' . $brand . '|' . $dosage . '|' . $strength);
      $productKey = substr(md5($keySource), 0, 12);

      $displayName = trim(($brand ? $brand . ' ' : '') . ($strength ? '(' . $strength . ') ' : '') . $generic);
      if ($displayName === '') $displayName = ($brand ?: 'Unnamed Product');

      if (isset($cart[$productKey])) {
        $cart[$productKey]['qty'] += $qty;
      } else {
        $cart[$productKey] = [
          'barcode' => $productKey,
          'name' => $displayName,
          'generic' => $generic,
          'brand' => $brand,
          'dosage' => $dosage,
          'strength' => $strength,
          'qty' => $qty
        ];
      }

      $flash = "Item added to order.";
      $flashType = "Success";
    }
  }

  // Remove item
  if ($action === 'remove_item') {
    $barcode = $_POST['barcode'] ?? '';
    if ($barcode !== '' && isset($cart[$barcode])) {
      unset($cart[$barcode]);
      $flash = "Item removed.";
      $flashType = "Success";
    }
  }

  // Receive PO (finalize)
  if ($action === 'receive_po') {
    if (count($cart) === 0) {
      $flash = "No items added yet. Add products to begin.";
      $flashType = "error";
    } else {
      // Clear cart and show success
      $_SESSION['po_cart'][$cartKey] = [];
      $flash = "✅ Purchase Order received successfully (demo mode).";
      $flashType = "Success";
    }
  }
}

$items = array_values($cart);
$itemCount = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Purchase Order</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { primary: '#274497' } } }
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
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
        <span>🏭</span> Suppliers
      </a>

      <a href="#"
         class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm bg-primary text-white">
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
            <input class="w-[420px] max-w-full text-sm outline-none" placeholder="Search..." />
          </div>

          <div class="flex items-center gap-3">
            <button class="p-2 rounded-lg hover:bg-gray-100" title="Notifications">🔔</button>
          </div>
        </div>
    </header>

    <main class="px-10 py-10">

      <!-- Title + Status -->
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-3xl font-bold">Create Purchase Order</h2>
          <p class="text-gray-500 mt-1">Scan items to add them to the receiving order.</p>
        </div>

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm font-semibold">
          <span class="w-2 h-2 bg-green-500 rounded-full"></span>
          System Online
        </div>
      </div>

      <?php if ($flash): ?>
        <div class="mt-6 px-4 py-3 rounded-xl border text-sm
          <?php echo $flashType === 'error'
            ? 'bg-red-50 border-red-200 text-red-700'
            : 'bg-green-50 border-green-200 text-green-700'; ?>">
          <?php echo h($flash); ?>
        </div>
      <?php endif; ?>

      <!-- Locked cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <div class="bg-white border rounded-2xl p-6">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-gray-500 tracking-wide">ORDER FROM</p>
            <div class="relative">
              <button id="supplierDropdownToggle" class="text-sm px-3 py-1 border rounded text-primary">Change</button>
            </div>
          </div>
          <div class="mt-4">
            <p class="text-lg font-bold" id="selectedSupplier"><?php echo h($supplierName); ?></p>
            <p class="text-sm text-gray-500 mt-1" id="selectedSupplierContact">
              <?php echo h($contactPerson); ?> • <?php echo h($contactPhone); ?>
            </p>
          </div>

          <!-- Dropdown Panel -->
          <div id="supplierDropdown" class="hidden mt-4 border rounded-lg bg-white shadow p-3">
            <input type="text" id="supplierSearch" placeholder="Search suppliers..." class="w-full px-3 py-2 border rounded mb-3 text-sm" />
            <div id="supplierList" class="max-h-48 overflow-y-auto space-y-2"></div>
          </div>
        </div>

        <div class="bg-white border rounded-2xl p-6">
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-gray-500 tracking-wide">RECEIVING BRANCH</p>
              <div class="relative">
                <button id="branchDropdownToggle" class="text-sm px-3 py-1 border rounded text-primary">Change</button>
              </div>
            </div>
            <div class="mt-4">
              <p class="text-lg font-bold" id="branchNameDisplay">Branch 1</p>
              <p class="text-sm text-gray-500 mt-1" id="branchAddressDisplay">1602 Eusebio Avenue, Pinagbuhatan Pasig City — Branch 1</p>
            </div>

            <!-- Branch dropdown panel (copied pattern from supplier) -->
            <div id="branchDropdown" class="hidden mt-4 border rounded-lg bg-white shadow p-3">
              <input type="text" id="branchSearch" placeholder="Search branches..." class="w-full px-3 py-2 border rounded mb-3 text-sm" />
              <div id="branchList" class="max-h-48 overflow-y-auto space-y-2"></div>
            </div>
        </div>
      </div>

      <!-- Product fields + Qty + Add -->
      <div class="bg-white border rounded-2xl p-6 mt-8">
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end" id="addItemForm">
          <input type="hidden" name="action" value="add_item" />
          <input type="hidden" name="supplier_id" value="<?php echo (int)$selectedSupplierId; ?>" />

          <div class="lg:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Item Type</label>
            <select id="itemTypeSelect" name="item_type" class="w-full px-4 py-3 border rounded-lg text-sm">
              <option value="medicine">Medicine</option>
              <option value="product">Product</option>
            </select>
          </div>

          <div class="lg:col-span-9" id="genericRow">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Generic Name</label>
            <div class="relative">
              <input id="genericInput" name="generic_name" type="text" autocomplete="off" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. Paracetamol" />
              <div id="productSuggestions" class="absolute left-0 right-0 mt-1 bg-white border rounded-lg shadow max-h-60 overflow-auto hidden z-50"></div>
            </div>
          </div>

          <div class="lg:col-span-6" id="productNameRow" style="display:none;">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name</label>
            <div class="relative">
              <input id="productNameInput" name="product_name" type="text" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. Multivitamins" />
              <div id="productSuggestionsProduct" class="absolute left-0 right-0 mt-1 bg-white border rounded-lg shadow max-h-60 overflow-auto hidden z-50"></div>
            </div>
          </div>

          <div class="lg:col-span-6" id="brandRow">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Brand Name</label>
            <input id="brandInput" name="brand_name" type="text" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. Acme" />
          </div>

          <div class="lg:col-span-3" id="unitRow" style="display:none;">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
            <input id="unitInput" name="unit" type="text" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. Bottle" />
          </div>

          <div class="lg:col-span-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dosage Form</label>
            <input id="dosageInput" name="dosage_form" type="text" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. Tablet" />
          </div>

          <div class="lg:col-span-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Strength</label>
            <input id="strengthInput" name="strength" type="text" class="w-full px-4 py-3 border rounded-lg text-sm" placeholder="e.g. 500 MG" />
          </div>

          <div class="lg:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
            <input name="qty" type="number" min="1" value="1" class="w-full border rounded-xl px-4 py-3 bg-gray-50 outline-none text-sm" />
          </div>

          <div class="lg:col-span-2">
            <button type="submit" class="w-full bg-primary text-white rounded-xl px-4 py-3 font-semibold hover:shadow-lg transition-all duration-300">Add Item</button>
          </div>
        </form>
      </div>

      <!-- Order Summary -->
      <div class="bg-white border rounded-2xl mt-8 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
          <p class="font-bold">Order Summary</p>
          <p class="text-sm text-gray-500"><?php echo $itemCount; ?> Items</p>
        </div>

        <?php if ($itemCount === 0): ?>
          <div class="py-16 text-center text-gray-500">
            <div class="text-5xl mb-3">📦</div>
            <p class="text-sm">No items added yet. Scan a barcode to begin.</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-white">
                <tr class="text-left text-gray-500">
                  <th class="px-6 py-4 font-semibold">#</th>
                  <th class="px-6 py-4 font-semibold">BARCODE</th>
                    <th class="px-6 py-4 font-semibold">PRODUCT NAME</th>
                    <th class="px-6 py-4 font-semibold">DOSAGE FORM</th>
                    <th class="px-6 py-4 font-semibold">QTY</th>
                  <th class="px-6 py-4 font-semibold text-right">ACTION</th>
                </tr>
              </thead>
              <tbody class="divide-y">
              <?php foreach ($items as $i => $row): ?>
                <tr>
                  <td class="px-6 py-4"><?php echo $i + 1; ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo h($row['barcode']); ?></td>
                    <td class="px-6 py-4 font-semibold"><?php echo h($row['name']); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo h($row['dosage'] ?? ''); ?></td>
                    <td class="px-6 py-4">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-blue-50 text-primary font-semibold text-xs">
                      <?php echo (int)$row['qty']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <form method="POST" class="inline">
                      <input type="hidden" name="action" value="remove_item">
                      <input type="hidden" name="barcode" value="<?php echo h($row['barcode']); ?>">
                      <button class="p-2 rounded-lg hover:bg-gray-100" title="Remove">🗑️</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <div class="px-6 py-5 border-t bg-white flex items-center justify-end">
          <button
            id="sendForVerificationBtn"
            class="px-6 py-3 rounded-xl font-semibold text-white bg-primary hover:shadow-lg hover:shadow-primary/30 hover:scale-105 transition-all duration-300 disabled:hover:scale-100 disabled:opacity-50"
            <?php echo $itemCount === 0 ? 'disabled' : ''; ?>
          >
            📨 Send to owner for verification
          </button>
        </div>
      </div>

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

  // Supplier dropdown toggle and search
  const supplierToggle = document.getElementById('supplierDropdownToggle');
  const supplierDropdown = document.getElementById('supplierDropdown');
  const supplierSearch = document.getElementById('supplierSearch');

  supplierToggle.addEventListener('click', () => {
    supplierDropdown.classList.toggle('hidden');
    supplierSearch.focus();
  });

  // Fetch suppliers from server by prefix and render list
  async function fetchSuppliers(q = '') {
    try {
      const res = await fetch('search_suppliers.php?q=' + encodeURIComponent(q));
      if (!res.ok) throw new Error('Network error');
      const data = await res.json();
      renderSupplierList(data);
    } catch (err) {
      const list = document.getElementById('supplierList');
      list.innerHTML = '<div class="p-2 text-sm text-red-500">Failed to load suppliers.</div>';
    }
  }

  function renderSupplierList(suppliers) {
    const list = document.getElementById('supplierList');
    list.innerHTML = '';
    if (!suppliers || suppliers.length === 0) {
      list.innerHTML = '<div class="p-2 text-sm text-gray-500">No suppliers found.</div>';
      return;
    }
    suppliers.forEach(s => {
      const div = document.createElement('div');
      div.className = 'p-2 rounded hover:bg-gray-50 flex items-center justify-between';

      const left = document.createElement('div');
      const name = document.createElement('div');
      name.className = 'font-semibold';
      name.textContent = s.supplier_name;
      const contact = document.createElement('div');
      contact.className = 'text-xs text-gray-500';
      contact.textContent = s.contact_number || '';
      left.appendChild(name);
      left.appendChild(contact);

      const right = document.createElement('div');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'text-sm text-primary px-3 py-1 border rounded';
      btn.textContent = 'Select';
      btn.addEventListener('click', () => selectSupplier(parseInt(s.supplier_id)));
      right.appendChild(btn);

      div.appendChild(left);
      div.appendChild(right);
      list.appendChild(div);
    });
  }

  // Load full list when opening the dropdown
  supplierToggle.addEventListener('click', () => {
    if (!supplierDropdown.classList.contains('hidden')) fetchSuppliers('');
  });

  supplierSearch.addEventListener('input', () => {
    const q = supplierSearch.value.trim();
    // Only prefix search — server will return suppliers starting with given string
    fetchSuppliers(q);
  });

  // --- Branch dropdown (copied pattern from supplier) ---
  const branchDropdownToggle = document.getElementById('branchDropdownToggle');
  const branchDropdown = document.getElementById('branchDropdown');
  const branchSearch = document.getElementById('branchSearch');
  const branchList = document.getElementById('branchList');
  const branchAddressDisplay = document.getElementById('branchAddressDisplay');
  const branchNameDisplay = document.getElementById('branchNameDisplay');

  const BRANCHES = [
    { id: 'Branch 1', name: 'Branch 1 (Main)', address: '1602 Eusebio Avenue, Pinagbuhatan Pasig City — Branch 1' },
    { id: 'Branch 2', name: 'Branch 2 (East)', address: '1602 Eusebio Avenue, Pinagbuhatan Pasig City — Branch 2' },
    { id: 'Branch 3', name: 'Branch 3 (West)', address: '1602 Eusebio Avenue, Pinagbuhatan Pasig City — Branch 3' }
  ];

  // keep current branch id in JS (no select element)
  let currentBranchId = BRANCHES[0].id;
  if (branchNameDisplay) branchNameDisplay.textContent = BRANCHES[0].name;
  if (branchAddressDisplay) branchAddressDisplay.textContent = BRANCHES[0].address;

  function renderBranchList(items) {
    branchList.innerHTML = '';
    if (!items || items.length === 0) {
      branchList.innerHTML = '<div class="p-2 text-sm text-gray-500">No branches found.</div>';
      return;
    }
    items.forEach(b => {
      const div = document.createElement('div');
      div.className = 'p-2 rounded hover:bg-gray-50 flex items-center justify-between';

      const left = document.createElement('div');
      const name = document.createElement('div');
      name.className = 'font-semibold';
      name.textContent = b.name;
      const contact = document.createElement('div');
      contact.className = 'text-xs text-gray-500';
      contact.textContent = b.address;
      left.appendChild(name);
      left.appendChild(contact);

      const right = document.createElement('div');
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'text-sm text-primary px-3 py-1 border rounded';
      btn.textContent = 'Select';
      btn.addEventListener('click', () => selectBranch(b.id, b.address));
      right.appendChild(btn);

      div.appendChild(left);
      div.appendChild(right);
      branchList.appendChild(div);
    });
  }

  function selectBranch(id, address) {
    currentBranchId = id;
    if (branchNameDisplay) {
      const found = BRANCHES.find(b => b.id === id);
      branchNameDisplay.textContent = found ? found.name : id;
    }
    if (branchAddressDisplay) branchAddressDisplay.textContent = address || '';
    branchDropdown.classList.add('hidden');
  }

  branchDropdownToggle?.addEventListener('click', () => {
    branchDropdown.classList.toggle('hidden');
    if (!branchDropdown.classList.contains('hidden')) {
      branchSearch.focus();
      renderBranchList(BRANCHES);
    }
  });

  branchSearch?.addEventListener('input', () => {
    const q = branchSearch.value.trim().toLowerCase();
    if (!q) return renderBranchList(BRANCHES);
    renderBranchList(BRANCHES.filter(b => b.name.toLowerCase().includes(q) || b.id.toLowerCase().includes(q)));
  });

  // (no select) keep display in sync when currentBranchId changes via selectBranch()

  // --- Product autocomplete ---
  const genericInput = document.getElementById('genericInput');
  const dosageInput = document.getElementById('dosageInput');
  const strengthInput = document.getElementById('strengthInput');
  const productNameInput = document.getElementById('productNameInput');
  const brandInput = document.getElementById('brandInput');
  let suggestionsBox = document.getElementById('productSuggestions');
  const suggestionsBoxProduct = document.getElementById('productSuggestionsProduct');

  let prodDebounce = null;

  function hideSuggestions() {
    if (suggestionsBox) { suggestionsBox.classList.add('hidden'); suggestionsBox.innerHTML = ''; }
    if (suggestionsBoxProduct) { suggestionsBoxProduct.classList.add('hidden'); suggestionsBoxProduct.innerHTML = ''; }
  }

  function showSuggestions(items) {
    const genBox = document.getElementById('productSuggestions');
    const isProduct = itemTypeSelect.value === 'product';
    const activeBox = isProduct ? suggestionsBoxProduct : genBox;
    
    if (!activeBox) return;
    
    // Clear the active box
    activeBox.innerHTML = '';
    
    if (!items || items.length === 0) {
      activeBox.innerHTML = '<div class="p-2 text-sm text-gray-500">No results found.</div>';
      activeBox.classList.remove('hidden');
      // Hide the other box
      if (isProduct && genBox) genBox.classList.add('hidden');
      if (!isProduct && suggestionsBoxProduct) suggestionsBoxProduct.classList.add('hidden');
      return;
    }
    
    items.forEach(it => {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'w-full text-left px-3 py-2 hover:bg-gray-50 flex flex-col';
      const title = document.createElement('div');
      title.className = 'font-semibold text-sm';
      title.textContent = it.display;
      const meta = document.createElement('div');
      meta.className = 'text-xs text-gray-500';
      let metaText = it.brand || '';
      if (it.dosage) metaText += (metaText ? ' • ' : '') + it.dosage;
      if (it.strength) metaText += (metaText ? ' • ' : '') + it.strength;
      meta.textContent = metaText;
      row.appendChild(title);
      row.appendChild(meta);
      row.addEventListener('click', () => {
        if (it.type === 'product') {
          document.getElementById('productNameInput').value = it.display || '';
          document.getElementById('unitInput').value = it.dosage || '';
          genericInput.value = '';
          if (brandInput) brandInput.value = it.brand || '';
          document.getElementById('itemTypeSelect').value = 'product';
          document.getElementById('genericRow').style.display = 'none';
          document.getElementById('productNameRow').style.display = '';
          document.getElementById('unitRow').style.display = '';
        } else {
          genericInput.value = it.generic || '';
          dosageInput.value = it.dosage || '';
          strengthInput.value = it.strength || '';
          if (brandInput) brandInput.value = it.brand || '';
          document.getElementById('productNameInput').value = '';
          document.getElementById('unitInput').value = '';
          document.getElementById('itemTypeSelect').value = 'medicine';
          document.getElementById('genericRow').style.display = '';
          document.getElementById('productNameRow').style.display = 'none';
          document.getElementById('unitRow').style.display = 'none';
        }
        hideSuggestions();
      });
      activeBox.appendChild(row);
    });

    // Show active box, hide inactive box
    activeBox.classList.remove('hidden');
    if (isProduct && genBox) genBox.classList.add('hidden');
    if (!isProduct && suggestionsBoxProduct) suggestionsBoxProduct.classList.add('hidden');
    suggestionsBox = activeBox;
  }

  // Item type toggle: show/hide product-only fields
  const itemTypeSelect = document.getElementById('itemTypeSelect');
  const productNameRow = document.getElementById('productNameRow');
  const unitRow = document.getElementById('unitRow');
  itemTypeSelect.addEventListener('change', () => {
    if (itemTypeSelect.value === 'product') {
      productNameRow.style.display = '';
      unitRow.style.display = '';
      genericInput.value = '';
      brandInput.value = '';
      dosageInput.value = '';
      strengthInput.value = '';
      // hide generic name when selecting product
      document.getElementById('genericRow').style.display = 'none';
      hideSuggestions();
      productNameInput.focus();
    } else {
      productNameRow.style.display = 'none';
      unitRow.style.display = 'none';
      productNameInput.value = '';
      unitInput.value = '';
      // show generic name for medicine
      document.getElementById('genericRow').style.display = '';
      hideSuggestions();
      genericInput.focus();
    }
  });

  // initialize visibility based on default value
  itemTypeSelect.dispatchEvent(new Event('change'));

  // Send to verification via AJAX
  const sendBtn = document.getElementById('sendForVerificationBtn');
  sendBtn.addEventListener('click', async () => {
    if (sendBtn.disabled) return;
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    try {
      const fd = new FormData();
      fd.append('action', 'send');
      fd.append('supplier_id', '<?php echo (int)$selectedSupplierId; ?>');
      // include receiving branch info (use currentBranchId)
      // Prefer the visible branch display (user selection), fallback to JS id / server default
      const branchEl = document.getElementById('branchNameDisplay');
      const branchName = (branchEl && branchEl.textContent && branchEl.textContent.trim())
        ? branchEl.textContent.trim()
        : ((typeof currentBranchId !== 'undefined' && currentBranchId) ? currentBranchId : '<?php echo h($branchName); ?>');
      fd.append('branch_name', branchName);
      const res = await fetch('verification.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const data = await res.json();
      if (data.success) {
        showAlert('success', data.message || 'Purchase Order sent for verification.', () => {
          // reload to clear cart display
          window.location = 'purchase_order.php?supplier_id=' + <?php echo (int)$selectedSupplierId; ?>;
        });
      } else {
        showAlert('error', data.message || 'Failed to send.', () => {
          sendBtn.disabled = false;
          sendBtn.textContent = '📨 Send to owner for verification';
        });
      }
    } catch (err) {
      showAlert('error', 'Network error while sending.', () => {
        sendBtn.disabled = false;
        sendBtn.textContent = '📨 Send to owner for verification';
      });
    }
  });

  // Simple reusable alert modal
  function showAlert(type, message, onClose) {
    let modal = document.getElementById('poAlertModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'poAlertModal';
      modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-6 hidden';
      modal.innerHTML = `
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="relative max-w-lg w-full bg-white rounded-xl shadow-lg overflow-hidden">
          <div id="poAlertContent" class="p-6"></div>
          <div class="px-6 py-4 border-t flex justify-end">
            <button id="poAlertOk" class="px-4 py-2 rounded bg-primary text-white font-semibold">OK</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      document.getElementById('poAlertOk').addEventListener('click', () => {
        modal.classList.add('hidden'); if (typeof onClose === 'function') onClose();
      });
      modal.addEventListener('click', (e) => { if (e.target === modal) { modal.classList.add('hidden'); if (typeof onClose === 'function') onClose(); } });
    }
    const content = document.getElementById('poAlertContent');
    const color = type === 'success' ? 'green' : 'red';
    content.innerHTML = `
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-full bg-${color}-100 flex items-center justify-center text-${color}-700 text-2xl">${type === 'success' ? '✓' : '!'}</div>
        <div>
          <h3 class="font-semibold text-lg">${type === 'success' ? 'Success' : 'Error'}</h3>
          <p class="text-sm text-gray-600 mt-1">${message}</p>
        </div>
      </div>`;
    modal.classList.remove('hidden');
  }

  async function fetchProducts(q) {
    try {
      // request type-aware results
      const type = itemTypeSelect ? itemTypeSelect.value : 'medicine';
      const res = await fetch('search_products.php?q=' + encodeURIComponent(q) + '&type=' + encodeURIComponent(type));
      if (!res.ok) throw new Error('Network');
      const data = await res.json();
      showSuggestions(data);
    } catch (err) {
      const box = (itemTypeSelect && itemTypeSelect.value === 'product') ? suggestionsBoxProduct : document.getElementById('productSuggestions');
      if (box) { box.innerHTML = '<div class="p-2 text-sm text-red-500">Failed to load products.</div>'; box.classList.remove('hidden'); suggestionsBox = box; }
    }
  }

  function onProductInput() {
    let q = '';
    let isProduct = itemTypeSelect.value === 'product';
    
    if (isProduct) {
      q = (productNameInput.value || '').trim();
    } else {
      q = (genericInput.value || '').trim();
    }
    
    if (q.length === 0) { 
      hideSuggestions(); 
      return; 
    }
    
    if (prodDebounce) clearTimeout(prodDebounce);
    prodDebounce = setTimeout(() => fetchProducts(q), 250);
  }

  genericInput.addEventListener('input', onProductInput);
  productNameInput.addEventListener('input', onProductInput);

  // Hide suggestions when clicking outside
  document.addEventListener('click', (ev) => {
    if (!(suggestionsBox && suggestionsBox.contains && suggestionsBox.contains(ev.target)) && ev.target !== genericInput && ev.target !== productNameInput) {
      hideSuggestions();
    }
  });

  function selectSupplier(supplierId) {
    // navigate to same page with supplier_id to update selection
    window.location = 'purchase_order.php?supplier_id=' + supplierId;
  }
</script>

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

<script>
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

<script>
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
      // local close (hide card) without server call
      if (action === 'close') {
        const card = btn.closest('[data-po-id]'); if (card) card.remove(); return;
      }
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
          else if (action === 'verify') { if (card) { const badge = card.querySelector('.po-status-badge'); if (badge) { badge.className = 'po-status-badge px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700'; badge.textContent = 'Verified'; } card.querySelectorAll('button[data-action]').forEach(b => b.disabled = true); } }
        } else { alert(data.message || 'Failed'); btn.disabled = false; }
      } catch (err) { alert('Network error'); btn.disabled = false; }
    });
  })();
</script>

</body>
</html>
