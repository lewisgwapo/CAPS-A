<?php
require 'db.php';

$today = date('Y-m-d');

/* ==========================================================
   MEDICINE MODULE (BATCH + FIFO)
========================================================== */

/* CREATE (ADD MEDICINE BATCH) */
if (isset($_POST['add_med_batch'])) {
    $medicineId = (int)($_POST['medicine_id'] ?? 0);
    $branchId   = (int)($_POST['branch_id'] ?? 0);
    $qtyAdd     = (int)($_POST['quantity'] ?? 0);
    $price      = (float)($_POST['price'] ?? 0);
    $expiry     = $_POST['expiry'] ?? null;

    if ($medicineId > 0 && $branchId > 0 && $qtyAdd > 0 && $expiry) {
        try {
            $pdo->beginTransaction();

            // Insert into medicine_batch (temporary supplier_id = 1)
            $stmt = $pdo->prepare("
                INSERT INTO medicine_batch
                (medicine_id, branch_id, supplier_id, expiry_date, quantity, unit_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$medicineId, $branchId, 1, $expiry, $qtyAdd, $price]);

            // Update medicine_in_branch.total_quantity (UPSERT style)
            $stmt = $pdo->prepare("
                INSERT INTO medicine_in_branch (medicine_id, branch_id, total_quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE total_quantity = total_quantity + VALUES(total_quantity)
            ");
            $stmt->execute([$medicineId, $branchId, $qtyAdd]);

            $pdo->commit();
            header("Location: index.php?tab=med");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Add batch failed: " . $e->getMessage());
        }
    }
}

/* DELETE MEDICINE BATCH (also updates medicine_in_branch.total_quantity) */
if (isset($_GET['delete_med_batch'])) {
    $batchId = (int)$_GET['delete_med_batch'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT medicine_id, branch_id, quantity FROM medicine_batch WHERE batch_id = ?");
        $stmt->execute([$batchId]);
        $batch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($batch) {
            $medicineId = (int)$batch['medicine_id'];
            $branchId   = (int)$batch['branch_id'];
            $qty        = (int)$batch['quantity'];

            // Deduct totals
            $stmt = $pdo->prepare("
                UPDATE medicine_in_branch
                SET total_quantity = total_quantity - ?
                WHERE medicine_id = ? AND branch_id = ?
            ");
            $stmt->execute([$qty, $medicineId, $branchId]);

            // If total <= 0 remove row
            $stmt = $pdo->prepare("
                DELETE FROM medicine_in_branch
                WHERE medicine_id = ? AND branch_id = ? AND total_quantity <= 0
            ");
            $stmt->execute([$medicineId, $branchId]);

            // Delete the batch
            $stmt = $pdo->prepare("DELETE FROM medicine_batch WHERE batch_id = ?");
            $stmt->execute([$batchId]);
        }

        $pdo->commit();
        header("Location: index.php?tab=med");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Delete batch failed: " . $e->getMessage());
    }
}

/* UPDATE MEDICINE BATCH (from modal) */
if (isset($_POST['update_med_batch'])) {
    $batchId = (int)($_POST['batch_id'] ?? 0);
    $expiry  = $_POST['expiry'] ?? null;
    $qtyNew  = (int)($_POST['quantity'] ?? 0);
    $price   = (float)($_POST['price'] ?? 0);

    if ($batchId > 0 && $expiry) {
        try {
            $pdo->beginTransaction();

            // Fetch old
            $stmt = $pdo->prepare("SELECT medicine_id, branch_id, quantity FROM medicine_batch WHERE batch_id = ?");
            $stmt->execute([$batchId]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$old) throw new Exception("Batch not found.");

            $medicineId = (int)$old['medicine_id'];
            $branchId   = (int)$old['branch_id'];
            $qtyOld     = (int)$old['quantity'];

            // Update batch
            $stmt = $pdo->prepare("
                UPDATE medicine_batch
                SET expiry_date = ?, quantity = ?, unit_price = ?
                WHERE batch_id = ?
            ");
            $stmt->execute([$expiry, $qtyNew, $price, $batchId]);

            // Adjust totals by difference
            $diff = $qtyNew - $qtyOld;
            $stmt = $pdo->prepare("
                UPDATE medicine_in_branch
                SET total_quantity = total_quantity + ?
                WHERE medicine_id = ? AND branch_id = ?
            ");
            $stmt->execute([$diff, $medicineId, $branchId]);

            // Clean totals if <= 0
            $stmt = $pdo->prepare("
                DELETE FROM medicine_in_branch
                WHERE medicine_id = ? AND branch_id = ? AND total_quantity <= 0
            ");
            $stmt->execute([$medicineId, $branchId]);

            $pdo->commit();
            header("Location: index.php?tab=med");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Update batch failed: " . $e->getMessage());
        }
    }
}

/* TRANSFER MEDICINE STOCK (from modal; splits batch) */
if (isset($_POST['transfer_med_stock'])) {
    $batchId     = (int)($_POST['batch_id'] ?? 0);
    $transferQty = (int)($_POST['transfer_qty'] ?? 0);
    $toBranchId  = (int)($_POST['to_branch_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM medicine_batch WHERE batch_id = ?");
        $stmt->execute([$batchId]);
        $src = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$src) throw new Exception("Batch not found.");
        if ($toBranchId <= 0) throw new Exception("Select a destination branch.");
        if ($toBranchId === (int)$src['branch_id']) throw new Exception("Cannot transfer to the same branch.");
        if ($transferQty <= 0) throw new Exception("Transfer quantity must be greater than 0.");
        if ($transferQty > (int)$src['quantity']) throw new Exception("Not enough stock in this batch.");

        $medicineId = (int)$src['medicine_id'];
        $fromBranch = (int)$src['branch_id'];
        $supplierId = (int)$src['supplier_id'];
        $expiryDate = $src['expiry_date'];
        $unitPrice  = $src['unit_price'];

        // Deduct from source batch
        $stmt = $pdo->prepare("UPDATE medicine_batch SET quantity = quantity - ? WHERE batch_id = ?");
        $stmt->execute([$transferQty, $batchId]);

        // Create new batch in target branch (same expiry, price)
        $stmt = $pdo->prepare("
            INSERT INTO medicine_batch (medicine_id, branch_id, supplier_id, expiry_date, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$medicineId, $toBranchId, $supplierId, $expiryDate, $transferQty, $unitPrice]);

        // Update totals: from branch -
        $stmt = $pdo->prepare("
            UPDATE medicine_in_branch
            SET total_quantity = total_quantity - ?
            WHERE medicine_id = ? AND branch_id = ?
        ");
        $stmt->execute([$transferQty, $medicineId, $fromBranch]);

        $stmt = $pdo->prepare("
            DELETE FROM medicine_in_branch
            WHERE medicine_id = ? AND branch_id = ? AND total_quantity <= 0
        ");
        $stmt->execute([$medicineId, $fromBranch]);

        // Update totals: to branch + (UPSERT)
        $stmt = $pdo->prepare("
            INSERT INTO medicine_in_branch (medicine_id, branch_id, total_quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE total_quantity = total_quantity + VALUES(total_quantity)
        ");
        $stmt->execute([$medicineId, $toBranchId, $transferQty]);

        $pdo->commit();
        header("Location: index.php?tab=med");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Transfer failed: " . $e->getMessage());
    }
}

/* READ MEDICINE INVENTORY (FIFO) */
$stmt = $pdo->query("
    SELECT 
        mb.batch_id,
        mb.medicine_id,
        m.generic_name,
        m.brand_name,
        m.dosage_form,
        m.strength,
        c.category_name,
        mb.quantity,
        mb.unit_price,
        mb.expiry_date,
        b.branch_name
    FROM medicine_batch mb
    JOIN medicine m ON mb.medicine_id = m.medicine_id
    JOIN category c ON m.category_id = c.category_id
    JOIN branch b ON mb.branch_id = b.branch_id
    ORDER BY mb.expiry_date ASC
");
$medInventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* MEDICINE DROPDOWNS: Generic -> Brand (with form/strength) */
$allMedicines = $pdo->query("
    SELECT medicine_id, generic_name, brand_name, dosage_form, strength
    FROM medicine
    ORDER BY generic_name ASC, brand_name ASC, dosage_form ASC, strength ASC
")->fetchAll(PDO::FETCH_ASSOC);

$genericRows = $pdo->query("
    SELECT DISTINCT generic_name
    FROM medicine
    ORDER BY generic_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$branches = $pdo->query("
    SELECT branch_id, branch_name
    FROM branch
    ORDER BY branch_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$genericToBrands = [];
foreach ($allMedicines as $m) {
    $g = $m['generic_name'] ?? '';
    if ($g === '') continue;
    $genericToBrands[$g][] = [
        'medicine_id' => (int)$m['medicine_id'],
        'brand_name'  => (string)($m['brand_name'] ?? ''),
        'dosage_form' => (string)($m['dosage_form'] ?? ''),
        'strength'    => (string)($m['strength'] ?? '')
    ];
}

/* ==========================================================
   PRODUCT MODULE (product + product_in_branch)
========================================================== */

/* ADD/UPSERT PRODUCT STOCK */
if (isset($_POST['add_product_stock'])) {
    $productId    = (int)($_POST['product_id'] ?? 0);
    $branchId     = (int)($_POST['branch_id'] ?? 0);
    $qtyAdd       = (int)($_POST['quantity'] ?? 0);
    $sellingPrice = (float)($_POST['selling_price'] ?? 0);

    if ($productId > 0 && $branchId > 0 && $qtyAdd > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO product_in_branch (product_id, branch_id, quantity, selling_price)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    quantity = quantity + VALUES(quantity),
                    selling_price = VALUES(selling_price)
            ");
            $stmt->execute([$productId, $branchId, $qtyAdd, $sellingPrice]);

            header("Location: index.php?tab=prod");
            exit;
        } catch (Exception $e) {
            die("Add product stock failed: " . $e->getMessage());
        }
    }
}

/* UPDATE PRODUCT STOCK (from modal) */
if (isset($_POST['update_product_stock'])) {
    $productBranchId = (int)($_POST['product_branch_id'] ?? 0);
    $qtyNew          = (int)($_POST['quantity'] ?? 0);
    $sellingPrice    = (float)($_POST['selling_price'] ?? 0);

    if ($productBranchId > 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE product_in_branch
                SET quantity = ?, selling_price = ?
                WHERE product_branch_id = ?
            ");
            $stmt->execute([$qtyNew, $sellingPrice, $productBranchId]);

            header("Location: index.php?tab=prod");
            exit;
        } catch (Exception $e) {
            die("Update product stock failed: " . $e->getMessage());
        }
    }
}

/* DELETE PRODUCT STOCK ROW */
if (isset($_GET['delete_product_stock'])) {
    $productBranchId = (int)$_GET['delete_product_stock'];

    try {
        $stmt = $pdo->prepare("DELETE FROM product_in_branch WHERE product_branch_id = ?");
        $stmt->execute([$productBranchId]);

        header("Location: index.php?tab=prod");
        exit;
    } catch (Exception $e) {
        die("Delete product stock failed: " . $e->getMessage());
    }
}

/* READ PRODUCT INVENTORY */
$stmt = $pdo->query("
    SELECT
        pib.product_branch_id,
        p.product_id,
        p.product_name,
        p.dosage_form,
        p.strength,
        p.unit,
        c.category_name,
        b.branch_name,
        pib.quantity,
        pib.selling_price
    FROM product_in_branch pib
    JOIN product p ON pib.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    JOIN branch b ON pib.branch_id = b.branch_id
    ORDER BY p.product_name ASC, b.branch_name ASC
");
$productInventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* PRODUCT DROPDOWN (show name + form + strength + unit) */
$products = $pdo->query("
    SELECT product_id, product_name, dosage_form, strength, unit
    FROM product
    ORDER BY product_name ASC, dosage_form ASC, strength ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* TAB DEFAULT */
$tab = $_GET['tab'] ?? 'med';
if (!in_array($tab, ['med', 'prod'], true)) $tab = 'med';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #CDCFD6; }
        .card-header { background-color: #274497; color: #fff; }
        .btn-main { background-color: #274497; color: #fff; }
        .btn-main:hover { background-color: #1f356f; color: #fff; }
        .nav-tabs .nav-link { color: #274497; font-weight: 600; }
        .nav-tabs .nav-link.active { background-color: #274497; color: #fff; border-color: #274497; }
        .expired { background-color: #f8d7da; }
        .low-stock { background-color: #fff3cd; }
        .table thead { background: #274497; color: #fff; }
        .small-muted { font-size: .85rem; color: rgba(0,0,0,.6); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Bowl of Hygea Pharmacy Inventory</h4>
        </div>

        <div class="card-body">

            <!-- TABS -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'med' ? 'active' : '' ?>" href="?tab=med">Medicine Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'prod' ? 'active' : '' ?>" href="?tab=prod">Products Inventory</a>
                </li>
            </ul>

            <?php if ($tab === 'med'): ?>
                <!-- ===================== MEDICINE TAB ===================== -->

                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Generic Name</label>
                        <select id="genericSelect" class="form-control" required>
                            <option value="">Select Generic</option>
                            <?php foreach ($genericRows as $g): ?>
                                <option value="<?= htmlspecialchars($g['generic_name']) ?>">
                                    <?= htmlspecialchars($g['generic_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Brand (Form & Strength)</label>
                        <select id="brandSelect" name="medicine_id" class="form-control" required disabled>
                            <option value="">Select Brand</option>
                        </select>
                        <div class="small-muted">Example: Biogesic (Tablet - 500mg)</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-control" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int)$b['branch_id'] ?>">
                                    <?= htmlspecialchars($b['branch_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" min="0" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Expiration Date</label>
                        <input type="date" name="expiry" class="form-control" required>
                    </div>

                    <div class="col-md-4 d-grid align-items-end">
                        <button type="submit" name="add_med_batch" class="btn btn-main">Add Batch</button>
                    </div>
                </form>

                <hr>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Generic</th>
                            <th>Brand</th>
                            <th>Form</th>
                            <th>Strength</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Expiry</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th style="width:180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($medInventory)): ?>
                        <tr><td colspan="12" class="text-center text-muted">No medicine batches found.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($medInventory as $i => $item):
                        $status = "OK";
                        $rowClass = "";
                        if ($item['expiry_date'] < $today) { $status = "Expired"; $rowClass = "expired"; }
                        elseif ((int)$item['quantity'] <= 5) { $status = "Low Stock"; $rowClass = "low-stock"; }
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($item['generic_name']) ?></td>
                            <td><?= htmlspecialchars($item['brand_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['dosage_form'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['strength'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['category_name']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td>₱<?= number_format((float)$item['unit_price'], 2) ?></td>
                            <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                            <td><?= htmlspecialchars($item['branch_name']) ?></td>
                            <td><strong><?= $status ?></strong></td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-main btnEditMed"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editMedModal"
                                    data-batchid="<?= (int)$item['batch_id'] ?>"
                                    data-generic="<?= htmlspecialchars($item['generic_name']) ?>"
                                    data-brand="<?= htmlspecialchars($item['brand_name'] ?? '') ?>"
                                    data-form="<?= htmlspecialchars($item['dosage_form'] ?? '') ?>"
                                    data-strength="<?= htmlspecialchars($item['strength'] ?? '') ?>"
                                    data-branch="<?= htmlspecialchars($item['branch_name']) ?>"
                                    data-qty="<?= (int)$item['quantity'] ?>"
                                    data-price="<?= (float)$item['unit_price'] ?>"
                                    data-expiry="<?= htmlspecialchars($item['expiry_date']) ?>"
                                >Edit</button>

                                <a href="?tab=med&delete_med_batch=<?= (int)$item['batch_id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this batch? This will also update branch totals.')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- ===================== PRODUCTS TAB ===================== -->

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product (Type)</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $p):
                                $name = $p['product_name'] ?? '';
                                $df   = $p['dosage_form'] ?? '';
                                $st   = $p['strength'] ?? '';
                                $unit = $p['unit'] ?? '';
                                $extra = trim(implode(' - ', array_filter([$df, $st, $unit])));
                                $label = $extra ? "{$name} ({$extra})" : $name;
                            ?>
                                <option value="<?= (int)$p['product_id'] ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-control" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= (int)$b['branch_id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Qty</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" min="0" required>
                    </div>

                    <div class="col-md-12 d-grid">
                        <button type="submit" name="add_product_stock" class="btn btn-main">Add / Restock Product</button>
                    </div>
                </form>

                <hr>

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Form</th>
                            <th>Strength</th>
                            <th>Unit</th>
                            <th>Category</th>
                            <th>Branch</th>
                            <th>Qty</th>
                            <th>Selling Price</th>
                            <th>Status</th>
                            <th style="width:180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($productInventory)): ?>
                        <tr><td colspan="11" class="text-center text-muted">No products in branches yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($productInventory as $i => $item):
                        $status = "OK";
                        $rowClass = "";
                        if ((int)$item['quantity'] <= 5) { $status = "Low Stock"; $rowClass = "low-stock"; }
                    ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['dosage_form'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['strength'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['category_name']) ?></td>
                            <td><?= htmlspecialchars($item['branch_name']) ?></td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td>₱<?= number_format((float)$item['selling_price'], 2) ?></td>
                            <td><strong><?= $status ?></strong></td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-main btnEditProd"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProdModal"
                                    data-pbid="<?= (int)$item['product_branch_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['product_name']) ?>"
                                    data-branch="<?= htmlspecialchars($item['branch_name']) ?>"
                                    data-qty="<?= (int)$item['quantity'] ?>"
                                    data-price="<?= (float)$item['selling_price'] ?>"
                                >Edit</button>

                                <a href="?tab=prod&delete_product_stock=<?= (int)$item['product_branch_id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this product stock row?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ===================== MEDICINE EDIT MODAL ===================== -->
<div class="modal fade" id="editMedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header text-white" style="background:#274497;">
        <h5 class="modal-title">Edit Medicine Batch / Transfer Stock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-3">
            <label class="form-label">Generic</label>
            <input type="text" id="mGeneric" class="form-control" disabled>
          </div>
          <div class="col-md-3">
            <label class="form-label">Brand</label>
            <input type="text" id="mBrand" class="form-control" disabled>
          </div>
          <div class="col-md-3">
            <label class="form-label">Form</label>
            <input type="text" id="mForm" class="form-control" disabled>
          </div>
          <div class="col-md-3">
            <label class="form-label">Strength</label>
            <input type="text" id="mStrength" class="form-control" disabled>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Branch</label>
          <input type="text" id="mBranch" class="form-control" disabled>
        </div>

        <div class="row">
          <div class="col-md-6">
            <h6 class="mb-2">Update Batch</h6>
            <form method="POST">
              <input type="hidden" name="batch_id" id="mBatchId">

              <div class="mb-2">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" id="mQty" class="form-control" min="0" required>
              </div>

              <div class="mb-2">
                <label class="form-label">Unit Price</label>
                <input type="number" step="0.01" name="price" id="mPrice" class="form-control" min="0" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Expiration Date</label>
                <input type="date" name="expiry" id="mExpiry" class="form-control" required>
              </div>

              <button type="submit" name="update_med_batch" class="btn btn-main w-100">Save Changes</button>
            </form>
          </div>

          <div class="col-md-6">
            <h6 class="mb-2">Transfer Stock to Another Branch</h6>
            <form method="POST">
              <input type="hidden" name="batch_id" id="tBatchId">

              <div class="mb-2">
                <label class="form-label">Transfer Quantity</label>
                <input type="number" name="transfer_qty" id="tQty" class="form-control" min="1" required>
                <small class="text-muted">Max: <span id="tMax"></span></small>
              </div>

              <div class="mb-3">
                <label class="form-label">To Branch</label>
                <select name="to_branch_id" class="form-control" required>
                  <option value="">Select branch</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= (int)$b['branch_id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="submit" name="transfer_med_stock" class="btn btn-main w-100">Transfer</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- ===================== PRODUCT EDIT MODAL ===================== -->
<div class="modal fade" id="editProdModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-white" style="background:#274497;">
        <h5 class="modal-title">Edit Product Stock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Product</label>
          <input type="text" id="pName" class="form-control" disabled>
        </div>

        <div class="mb-3">
          <label class="form-label">Branch</label>
          <input type="text" id="pBranch" class="form-control" disabled>
        </div>

        <form method="POST">
          <input type="hidden" name="product_branch_id" id="pPBID">

          <div class="mb-2">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" id="pQty" class="form-control" min="0" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Selling Price</label>
            <input type="number" step="0.01" name="selling_price" id="pPrice" class="form-control" min="0" required>
          </div>

          <button type="submit" name="update_product_stock" class="btn btn-main w-100">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ===== Medicine dropdown dependent brand =====
  const genericToBrands = <?= json_encode($genericToBrands, JSON_UNESCAPED_UNICODE) ?>;
  const genericSelect = document.getElementById('genericSelect');
  const brandSelect = document.getElementById('brandSelect');

  function resetBrands() {
    if (!brandSelect) return;
    brandSelect.innerHTML = '<option value="">Select Brand</option>';
    brandSelect.disabled = true;
  }

  if (genericSelect) {
    genericSelect.addEventListener('change', () => {
      const g = genericSelect.value;
      resetBrands();

      if (!g || !genericToBrands[g] || genericToBrands[g].length === 0) return;

      genericToBrands[g].forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.medicine_id;

        const bn = (item.brand_name && item.brand_name.trim() !== '') ? item.brand_name : 'N/A';
        const df = (item.dosage_form && item.dosage_form.trim() !== '') ? item.dosage_form : '';
        const st = (item.strength && item.strength.trim() !== '') ? item.strength : '';
        const extra = [df, st].filter(Boolean).join(' - ');

        opt.textContent = extra ? `${bn} (${extra})` : bn;
        brandSelect.appendChild(opt);
      });

      brandSelect.disabled = false;
    });
  }

  // ===== Medicine modal =====
  document.querySelectorAll('.btnEditMed').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('mBatchId').value = btn.dataset.batchid;
      document.getElementById('tBatchId').value = btn.dataset.batchid;

      document.getElementById('mGeneric').value = btn.dataset.generic || '';
      document.getElementById('mBrand').value = btn.dataset.brand || '';
      document.getElementById('mForm').value = btn.dataset.form || '';
      document.getElementById('mStrength').value = btn.dataset.strength || '';
      document.getElementById('mBranch').value = btn.dataset.branch || '';

      document.getElementById('mQty').value = btn.dataset.qty || 0;
      document.getElementById('mPrice').value = btn.dataset.price || 0;
      document.getElementById('mExpiry').value = btn.dataset.expiry || '';

      document.getElementById('tQty').value = '';
      document.getElementById('tMax').textContent = btn.dataset.qty || 0;
      document.getElementById('tQty').setAttribute('max', btn.dataset.qty || 0);
    });
  });

  // ===== Product modal =====
  document.querySelectorAll('.btnEditProd').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('pPBID').value = btn.dataset.pbid;
      document.getElementById('pName').value = btn.dataset.name || '';
      document.getElementById('pBranch').value = btn.dataset.branch || '';
      document.getElementById('pQty').value = btn.dataset.qty || 0;
      document.getElementById('pPrice').value = btn.dataset.price || 0;
    });
  });
</script>

</body>
</html>
