<?php
// search_products.php
require_once __DIR__ . "/db/hygeadb.php";

header('Content-Type: application/json; charset=utf-8');

// query and optional type (medicine|product). If type provided, return only that type.
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
if ($q === '') {
  echo json_encode([]);
  exit;
}

$prefix = $q . '%';
$out = [];

// If type is 'product', only query product table
if ($type === 'product') {
  // Search product table (product_name)
  $stmt2 = $conn->prepare("SELECT product_name, unit, barcode FROM product WHERE product_name LIKE ? LIMIT 200");
  if ($stmt2) {
    $stmt2->bind_param('s', $prefix);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
      $out[] = [
        'type' => 'product',
        'display' => $r['product_name'],
        'generic' => '',
        'brand' => $r['product_name'],
        'dosage' => $r['unit'] ?? '',
        'strength' => '',
        'barcode' => $r['barcode'] ?? ''
      ];
    }
  }
}

// If type is 'medicine', only query medicine table
if ($type === 'medicine') {
  // Search medicine table (generic_name, brand_name)
  $stmt = $conn->prepare("SELECT generic_name, brand_name, dosage_form, strength, barcode FROM medicine WHERE generic_name LIKE ? OR brand_name LIKE ? LIMIT 200");
  if ($stmt) {
    $stmt->bind_param('ss', $prefix, $prefix);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
      $display = trim((($r['brand_name'] ? $r['brand_name'] . ' ' : '') . ($r['strength'] ? '(' . $r['strength'] . ') ' : '') . $r['generic_name']));
      $out[] = [
        'type' => 'medicine',
        'display' => $display,
        'generic' => $r['generic_name'],
        'brand' => $r['brand_name'],
        'dosage' => $r['dosage_form'],
        'strength' => $r['strength'],
        'barcode' => $r['barcode'] ?? ''
      ];
    }
  }
}

// Sort alphabetically by display (case-insensitive)
usort($out, function($a, $b){
  return strcasecmp($a['display'], $b['display']);
});

echo json_encode($out, JSON_UNESCAPED_UNICODE);
exit;
