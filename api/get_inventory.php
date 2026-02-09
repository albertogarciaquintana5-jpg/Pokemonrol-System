<?php
header('Content-Type: application/json');
session_start();
try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = (int)$_SESSION['user']['id'];

$columnExistsStmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
$hasEffectCols = false;
if ($columnExistsStmt) {
    $t = 'items'; $c = 'effect_type'; $columnExistsStmt->bind_param('ss', $t, $c); $columnExistsStmt->execute(); $cres = $columnExistsStmt->get_result(); $has = $cres->fetch_assoc(); if ($has && (int)$has['c'] > 0) $hasEffectCols = true;
    $columnExistsStmt->close();
}

if ($hasEffectCols) {
    $sql = "SELECT i.cantidad, i.item_id, it.nombre, it.clave, it.icono, it.descripcion, it.effect_type, it.effect_value
        FROM inventario i
        JOIN items it ON it.id = i.item_id
        WHERE i.user_id = ?";
} else {
    $sql = "SELECT i.cantidad, i.item_id, it.nombre, it.clave, it.icono, it.descripcion
        FROM inventario i
        JOIN items it ON it.id = i.item_id
        WHERE i.user_id = ?";
}
if (!$stmt = $mysqli->prepare($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed']);
    exit;
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'items' => $items]);
exit;
?>