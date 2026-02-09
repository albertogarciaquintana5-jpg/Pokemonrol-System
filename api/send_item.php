<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$item_id = isset($input['item_id']) ? (int)$input['item_id'] : 0;
$to_email = isset($input['to_email']) ? trim($input['to_email']) : '';
if ($item_id <= 0 || empty($to_email)) { http_response_code(400); echo json_encode(['error' => 'Invalid params']); exit; }

// Find recipient
$stmt = $mysqli->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1'); $stmt->bind_param('s', $to_email); $stmt->execute(); $r = $stmt->get_result(); $rec = $r->fetch_assoc(); $stmt->close();
if (!$rec) { http_response_code(404); echo json_encode(['error' => 'Recipient not found']); exit; }
$recipient_id = (int)$rec['id'];

// Atomic move: decrease sender, increase recipient
$mysqli->begin_transaction();
try {
    // decrease sender
    $upd = $mysqli->prepare('UPDATE inventario SET cantidad = cantidad - 1 WHERE user_id = ? AND item_id = ? AND cantidad > 0');
    $upd->bind_param('ii', $user_id, $item_id); $upd->execute();
    if ($upd->affected_rows <= 0) { $mysqli->rollback(); echo json_encode(['error' => 'No hay unidades suficientes']); exit; }
    $upd->close();
    // If sender reached zero for this item, remove the row
    $delSender = $mysqli->prepare('DELETE FROM inventario WHERE user_id = ? AND item_id = ? AND cantidad <= 0');
    if ($delSender) { $delSender->bind_param('ii', $user_id, $item_id); $delSender->execute(); $delSender->close(); }
    // increase recipient
    $ins = $mysqli->prepare('INSERT INTO inventario (user_id, item_id, cantidad) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE cantidad = cantidad + 1');
    $ins->bind_param('ii', $recipient_id, $item_id); $ins->execute(); $ins->close();
    $mysqli->commit();
} catch (Exception $e) { $mysqli->rollback(); http_response_code(500); echo json_encode(['error'=>'Transaction failed: ' . $e->getMessage()]); exit; }

// Return updated inventory of sender (include effect metadata if available)
$columnExistsStmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
$hasEffectCols = false;
if ($columnExistsStmt) {
    $t = 'items'; $c = 'effect_type'; $columnExistsStmt->bind_param('ss', $t, $c); $columnExistsStmt->execute(); $cres = $columnExistsStmt->get_result(); $has = $cres->fetch_assoc(); if ($has && (int)$has['c'] > 0) $hasEffectCols = true;
    $columnExistsStmt->close();
}
if ($hasEffectCols) {
    $invSql = 'SELECT i.item_id, i.cantidad, it.nombre, it.effect_type, it.effect_value FROM inventario i JOIN items it ON it.id=i.item_id WHERE i.user_id = ?';
} else {
    $invSql = 'SELECT i.item_id, i.cantidad, it.nombre FROM inventario i JOIN items it ON it.id=i.item_id WHERE i.user_id = ?';
}
$stmt = $mysqli->prepare($invSql);
$stmt->bind_param('i', $user_id);
$stmt->execute(); $res = $stmt->get_result(); $inv = []; while ($r = $res->fetch_assoc()) $inv[] = $r; $stmt->close();
echo json_encode(['success'=>true, 'inventory'=>$inv, 'message'=>'Item enviado']);
exit;
?>
