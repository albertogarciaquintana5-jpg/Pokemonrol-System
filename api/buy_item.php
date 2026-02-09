<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
  echo json_encode(['success' => false, 'error' => 'No autenticado']); exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$user_id = (int)$_SESSION['user']['id'];
$item_clave = isset($data['clave']) ? trim($data['clave']) : null;
$quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;

if (!$item_clave) { echo json_encode(['success' => false, 'error' => 'Item inválido']); exit; }

// Obtener precio y id del item desde la BD
$it = $mysqli->prepare("SELECT id, nombre, price FROM items WHERE clave = ? LIMIT 1");
if (!$it) { echo json_encode(['success' => false, 'error' => 'Error interno DB']); exit; }
$it->bind_param('s', $item_clave);
$it->execute();
$res_price = $it->get_result();
if (!$res_price || $res_price->num_rows === 0) { echo json_encode(['success' => false, 'error' => 'Item no encontrado en la DB']); exit; }
$itemRow = $res_price->fetch_assoc();
$item_id = (int)$itemRow['id'];
$item_name = $itemRow['nombre'] ?? $item_clave;
$price = isset($itemRow['price']) ? (float)$itemRow['price'] : null;
$it->close();

if ($price === null) { echo json_encode(['success' => false, 'error' => 'Este item no tiene precio asignado']); exit; }

$total = $price * $quantity;

// Transacción: comprobar saldo, descontar y añadir al inventario
$mysqli->begin_transaction();
try {
  // Lock user row
  $stmt = $mysqli->prepare("SELECT money FROM usuarios WHERE id = ? FOR UPDATE");
  $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result();
  if (!$res || $res->num_rows === 0) throw new Exception('Usuario no encontrado');
  $row = $res->fetch_assoc();
  $current = (float)$row['money'];
  $stmt->close();

  if ($current < $total) throw new Exception('Fondos insuficientes');

  $new = $current - $total;
  $upd = $mysqli->prepare("UPDATE usuarios SET money = ? WHERE id = ?");
  $upd->bind_param('di', $new, $user_id);
  if (!$upd->execute()) throw new Exception('Error actualizando saldo');
  $upd->close();
  // Upsert inventario
  // Upsert inventario atomically to avoid race conditions that cause duplicate PK errors
  $i2 = $mysqli->prepare("INSERT INTO inventario (user_id, item_id, cantidad) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)");
  if (!$i2) throw new Exception('Error preparando inventario');
  $i2->bind_param('iii', $user_id, $item_id, $quantity);
  if (!$i2->execute()) throw new Exception('Error actualizando inventario');
  $i2->close();

  // Registrar transacción
  $ins = $mysqli->prepare("INSERT INTO money_transactions (user_id, amount, type, meta) VALUES (?, ?, ?, ?)");
  $type = 'purchase';
  $meta = json_encode(['item' => $item_clave, 'qty' => $quantity, 'unit_price' => $price]);
  if ($ins) { $ins->bind_param('idss', $user_id, $total, $type, $meta); $ins->execute(); $ins->close(); }

  $mysqli->commit();

  // Devolver saldo y nueva cantidad
  $resInv = $mysqli->prepare("SELECT cantidad FROM inventario WHERE user_id = ? AND item_id = ? LIMIT 1");
  $resInv->bind_param('ii', $user_id, $item_id); $resInv->execute(); $r4 = $resInv->get_result(); $iq = 0; if ($r4 && $r4->num_rows>0) { $iq = (int)$r4->fetch_assoc()['cantidad']; } $resInv->close();

  echo json_encode(['success' => true, 'balance' => number_format($new, 2, '.', ''), 'raw_balance' => $new, 'item' => $item_clave, 'item_id' => $item_id, 'new_qty' => $iq]);
} catch (Exception $e) {
  $mysqli->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
