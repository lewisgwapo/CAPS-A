<?php
// Initialize session for sales tracking
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "hygeadb";

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn && $conn->connect_error) {
    // if connection fails, set to null so rest of page can still render
    $conn = null;
} elseif ($conn) {
    $conn->set_charset("utf8mb4");
}

// Load product catalog (medicines + products) for display
$catalog = [];
if (isset($conn) && $conn) {
    // medicines
    $res = $conn->query("SELECT medicine_id, generic_name, brand_name, dosage_form, strength, barcode, category_id FROM medicine ORDER BY generic_name ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $name = trim($r['generic_name'] ?: $r['brand_name']);
            if ($name !== '') $catalog[$name] = [
                'name' => $name,
                'medicine_id' => $r['medicine_id'],
                'dosage_form' => $r['dosage_form'],
                'strength' => $r['strength'],
                'barcode' => $r['barcode'],
                'category_id' => $r['category_id']
            ];
        }
        $res->free();
    }

    // products
    $res = $conn->query("SELECT product_name, barcode FROM product ORDER BY product_name ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $name = trim($r['product_name']);
            if ($name !== '') $catalog[$name] = ['name' => $name, 'barcode' => $r['barcode'] ?? ''];
        }
        $res->free();
    }

    // normalize to array
    $catalog = array_values($catalog);
}

// Initialize cart if not exists
if  (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['grandTotal'] = 0;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $dbRequired = in_array($action, ['addItem','lookupBarcode','searchProducts']);
    if ($dbRequired && (!isset($conn) || $conn === null)) {
        echo json_encode(['success' => false, 'error' => 'Database not connected']);
        exit;
    }
    
    if ($_POST['action'] === 'addItem') {
        $product = $_POST['product'] ?? '';
        $qty = intval($_POST['qty'] ?? 0);
        $discountRate = floatval($_POST['discount'] ?? 0);

        if ($product && $qty > 0) {
            // fetch medicine full metadata if available
            $stmt = $conn->prepare("SELECT medicine_id, generic_name, brand_name, dosage_form, strength, barcode, category_id FROM medicine WHERE generic_name = ? OR brand_name = ? LIMIT 1");
            $stmt->bind_param("ss", $product, $product);
            $stmt->execute();
            $result = $stmt->get_result();
            $medicine = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            // if not medicine, try product table
            $prod = null;
            if (!$medicine) {
                $stmt = $conn->prepare("SELECT product_id, product_name, barcode FROM product WHERE product_name = ? LIMIT 1");
                $stmt->bind_param("s", $product);
                $stmt->execute();
                $r = $stmt->get_result();
                $prod = $r ? $r->fetch_assoc() : null;
                $stmt->close();
            }

            if (!$medicine && !$prod) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            // determine price: oldest batch (for medicine) -> product_in_branch -> default
            $price = null;
            if ($medicine) {
                $stmt = $conn->prepare("SELECT unit_price FROM medicine_batch WHERE medicine_id = ? AND quantity > 0 ORDER BY expiry_date ASC LIMIT 1");
                $stmt->bind_param("i", $medicine['medicine_id']);
                $stmt->execute();
                $batchResult = $stmt->get_result();
                $batchData = $batchResult ? $batchResult->fetch_assoc() : null;
                $stmt->close();
                if ($batchData) $price = floatval($batchData['unit_price']);

                if ($price === null) {
                    $stmt = $conn->prepare("SELECT pib.selling_price FROM product_in_branch pib JOIN product p ON pib.product_id = p.product_id WHERE p.product_name = ? LIMIT 1");
                    $stmt->bind_param('s', $product);
                    $stmt->execute();
                    $r = $stmt->get_result();
                    $pib = $r ? $r->fetch_assoc() : null;
                    $stmt->close();
                    $price = $pib ? floatval($pib['selling_price']) : 15.00;
                }
            } else {
                // product fallback
                $stmt = $conn->prepare("SELECT pib.selling_price FROM product_in_branch pib JOIN product p ON pib.product_id = p.product_id WHERE p.product_name = ? LIMIT 1");
                $stmt->bind_param('s', $product);
                $stmt->execute();
                $r = $stmt->get_result();
                $pib = $r ? $r->fetch_assoc() : null;
                $stmt->close();
                $price = $pib ? floatval($pib['selling_price']) : 15.00;
            }

            $subtotal = $price * $qty;
            $discount = $subtotal * $discountRate;
            $total = $subtotal - $discount;

            // prepare cart item and include metadata
            $item = [
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                'discount' => $discount,
                'total' => $total,
                'medicine_id' => $medicine['medicine_id'] ?? null,
                'dosage_form' => $medicine['dosage_form'] ?? null,
                'strength' => $medicine['strength'] ?? null,
                'barcode' => $medicine['barcode'] ?? ($prod['barcode'] ?? null),
                'category_id' => $medicine['category_id'] ?? null
            ];

            $_SESSION['cart'][] = $item;
            $_SESSION['grandTotal'] = array_sum(array_column($_SESSION['cart'], 'total'));

            echo json_encode(['success' => true, 'subtotal' => $subtotal, 'discount' => $discount, 'total' => $total, 'price' => $price, 'qty' => $qty, 'discountRate' => $discountRate, 'item' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid product or quantity']);
        }
        exit;
    }

    if ($_POST['action'] === 'lookupBarcode') {
        $barcode = $_POST['barcode'] ?? '';
        
        $stmt = $conn->prepare("SELECT generic_name, brand_name FROM medicine WHERE barcode = ? LIMIT 1");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $result = $stmt->get_result();
        $medicine = $result->fetch_assoc();
        $stmt->close();

        if ($medicine) {
            $productName = $medicine['generic_name'] ?: $medicine['brand_name'];
            echo json_encode(['success' => true, 'product' => $productName]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Barcode not found']);
        }
        exit;
    }

    if ($_POST['action'] === 'searchProducts') {
        $search = $_POST['query'] ?? '';
        
        $query = "SELECT DISTINCT generic_name, brand_name FROM medicine WHERE generic_name LIKE ? OR brand_name LIKE ? LIMIT 10";
        $searchTerm = "%$search%";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $name = $row['generic_name'] ?: $row['brand_name'];
            if (!in_array($name, $products)) {
                $products[] = $name;
            }
        }
        $stmt->close();
        
        echo json_encode(['products' => $products]);
        exit;
    }

    

    if ($_POST['action'] === 'pay') {
        $_SESSION['cart'] = [];
        $_SESSION['grandTotal'] = 0;
        echo json_encode(['success' => true]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales POS | Bowl of Hygea Pharmacy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Tailwind -->
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

<body class="bg-lightgray min-h-screen">

<!-- TOP NAV -->
<header class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-primary">Bowl of Hygea Pharmacy</h1>
        <p class="text-xs text-gray-500">Sales & POS Module</p>
    </div>
    <div class="text-sm text-gray-600">
        <?php echo date('M d, Y'); ?> • Admin
    </div>
</header>

<div class="flex">

<!-- SIDEBAR -->
<aside class="w-64 bg-lightgray text-gray-900 min-h-screen p-6 hidden md:block">
    <h2 class="text-lg font-bold mb-6 text-primary">Navigation</h2>
    <nav class="space-y-3 text-sm">
        <a href="dashboard.php" class="block hover:bg-gray-300 px-3 py-2 rounded text-gray-700">Dashboard</a>
        <a href="sales.php" class="block bg-primary text-white px-3 py-2 rounded font-semibold">Sales (POS)</a>
        <a href="inventory.php" class="block hover:bg-gray-300 px-3 py-2 rounded text-gray-700">Inventory</a>
        <a href="suppliers.php" class="block hover:bg-gray-300 px-3 py-2 rounded text-gray-700">Suppliers</a>
        <a href="login.php" class="block hover:bg-red-600 px-3 py-2 rounded mt-6 text-gray-700">Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">

<!-- POS ENTRY -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h2 class="text-xl font-bold text-primary mb-4">🧾 Point of Sale</h2>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="text-sm font-semibold">Barcode</label>
            <input id="barcode" class="w-full border rounded-lg px-3 py-2 mt-1" placeholder="Scan barcode" autofocus>
        </div>

        <div>
            <label class="text-sm font-semibold">Product</label>
            <input id="product" class="w-full border rounded-lg px-3 py-2 mt-1" list="productList" placeholder="Type product name">
            <datalist id="productList"></datalist>
        </div>

        <!-- Batch removed: using oldest available batch or fallback price -->

        <div>
            <label class="text-sm font-semibold">Quantity</label>
            <input type="number" id="qty" value="1" min="1"
                   class="w-full border rounded-lg px-3 py-2 mt-1">
        </div>

        <div>
            <label class="text-sm font-semibold">Discount</label>
            <select id="discount" class="w-full border rounded-lg px-3 py-2 mt-1">
                <option value="0">None</option>
                <option value="0.20">Senior / PWD (20%)</option>
            </select>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button onclick="addItem()"
            class="bg-primary text-white px-5 py-2 rounded-lg hover:opacity-90">
            ➕ Add Item
        </button>
        <button onclick="clearCart()"
            class="bg-red-500 text-white px-5 py-2 rounded-lg hover:bg-red-600">
            🗑 Clear
        </button>
    </div>
</div>

<!-- INVOICE -->
<div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-xl font-bold text-primary mb-4">📄 Sales Invoice</h2>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="p-3">Item</th>
                        <th>Qty</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <tr class="border-b text-center">
                    <td class="p-2 text-left">
                        <?php echo htmlspecialchars($item['product']); ?>
                        <?php
                            $meta = array_filter([
                                $item['strength'] ?? null,
                                $item['dosage_form'] ?? null,
                                $item['barcode'] ?? null
                            ]);
                            if (!empty($meta)) {
                                echo '<div class="text-xs text-gray-500">' . htmlspecialchars(implode(' | ', $meta)) . '</div>';
                            }
                        ?>
                    </td>
                        <td><?php echo $item['qty']; ?></td>
                    <td>₱<?php echo number_format($item['price'],2); ?></td>
                    <td>₱<?php echo number_format($item['discount'],2); ?></td>
                    <td class="font-semibold">₱<?php echo number_format($item['total'],2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold text-center">
                    <td colspan="4" class="p-3">Grand Total</td>
                    <td>₱<?php echo number_format($_SESSION['grandTotal'],2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-6 flex gap-4">
        <button onclick="pay('Cash')" class="bg-primary text-white px-6 py-2 rounded-lg">
            💵 Cash
        </button>
        <button onclick="pay('Digital Wallet')" class="bg-primary text-white px-6 py-2 rounded-lg">
            📱 Digital Wallet
        </button>
    </div>
</div>

</main>
</div>

<script>
function addItem() {
    const productEl = document.getElementById('product');
    const qtyEl = document.getElementById('qty');
    const discountEl = document.getElementById('discount');

    if (!productEl.value) {
        alert('Please select product');
        return;
    }

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "addItem",
            product: productEl.value,
            qty: qtyEl.value,
            discount: discountEl.value
        })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            console.log('addItem result', data);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to add item'));
        }
    });
}

function clearCart() {
    location.reload();
}

function pay(method) {
    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "pay", method })
    }).then(() => {
        alert("Payment Successful");
        location.reload();
    });
}

// Product search with autocomplete
document.getElementById('product').addEventListener('input', function(e) {
    const query = this.value.trim();
    if (query.length < 1) return;

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: 'searchProducts', query: query })
    }).then(r => r.json()).then(data => {
        const datalist = document.getElementById('productList');
        datalist.innerHTML = '';
        data.products.forEach(product => {
            const option = document.createElement('option');
            option.value = product;
            datalist.appendChild(option);
        });
    });
});

// Batch selection removed — system will use available batch price or fallback price automatically.

// Barcode scanner handling: scanners typically send the barcode followed by Enter
document.getElementById('barcode').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = this.value.trim();
        if (!code) return;

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: 'lookupBarcode', barcode: code })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                // Fill product, trigger batch load
                document.getElementById('product').value = data.product;
                document.getElementById('product').dispatchEvent(new Event('change'));
                document.getElementById('qty').value = 1;
                
                // Auto-add after a short delay to let batch load
                setTimeout(() => { addItem(); }, 200);
            } else {
                alert('Barcode not found');
            }
            this.value = '';
            this.focus();
        }).catch(err => {
            alert('Error: ' + err);
            this.value = '';
            this.focus();
        });
    }
});
</script>

</body>
</html>