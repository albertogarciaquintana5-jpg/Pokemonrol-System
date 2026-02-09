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
$amount = isset($data['amount']) ? (float)$data['amount'] : null;
$action = isset($data['action']) ? $data['action'] : 'add'; // 'add' o 'sub'

if ($amount === null || !is_numeric($amount) || $amount <= 0) {
  echo json_encode(['success' => false, 'error' => 'Cantidad inválida']); exit;
}

// Seguridad: límites razonables
if ($amount > 1000000) { echo json_encode(['success' => false, 'error' => 'Cantidad demasiado grande']); exit; }

// Realizamos la operación en transacción para evitar condiciones de carrera
$mysqli->begin_transaction();
try {
  // Lock the row
  $sql = "SELECT money FROM usuarios WHERE id = ? FOR UPDATE";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if (!$res || $res->num_rows === 0) throw new Exception('Usuario no encontrado');
  $row = $res->fetch_assoc();
  $current = (float)$row['money'];
  $stmt->close();

  if ($action === 'sub') {
    $new = $current - $amount;
    if ($new < 0) throw new Exception('Fondos insuficientes');
  } else {
    $new = $current + $amount;
  }

  $upd = $mysqli->prepare("UPDATE usuarios SET money = ? WHERE id = ?");
  $upd->bind_param('di', $new, $user_id);
  if (!$upd->execute()) throw new Exception('Error actualizando saldo');
  $upd->close();

  // Insert transaction record (opcional)
  $ins = $mysqli->prepare("INSERT INTO money_transactions (user_id, amount, type, meta) VALUES (?, ?, ?, ?)");
  $type = ($action === 'sub' ? 'debit' : 'credit');
  $meta = isset($data['meta']) ? json_encode($data['meta']) : null;
  if ($ins) { $ins->bind_param('idss', $user_id, $amount, $type, $meta); $ins->execute(); $ins->close(); }

  $mysqli->commit();

  echo json_encode(['success' => true, 'balance' => number_format($new, 2, '.', ''), 'raw_balance' => $new]);
} catch (Exception $e) {
  $mysqli->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>
