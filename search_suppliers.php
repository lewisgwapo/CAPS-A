<?php
// search_suppliers.php
require_once __DIR__ . "/db/hygeadb.php";

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Use prefix search for supplier names (case-insensitive depending on DB collation)
$prefix = $q . '%';

$stmt = $conn->prepare("SELECT supplier_id, supplier_name, contact_number FROM supplier WHERE supplier_name LIKE ? ORDER BY supplier_name LIMIT 200");
if ($stmt) {
  $stmt->bind_param('s', $prefix);
  $stmt->execute();
  $res = $stmt->get_result();
  $out = [];
  while ($row = $res->fetch_assoc()) {
    $out[] = $row;
  }
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
  exit;
}

// Fallback: empty array
echo json_encode([]);
exit;
