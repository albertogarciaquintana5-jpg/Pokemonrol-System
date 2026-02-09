<?php
// API: Actualizar dinero de un jugador
ob_start();
header('Content-Type: application/json');
error_reporting(0);

session_start();
include '../db.php';
ob_clean();

// Verificar que sea el admin
if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$money = isset($input['money']) ? (float)$input['money'] : 0;

if ($user_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
  exit;
}

if ($money < 0) {
  echo json_encode(['success' => false, 'error' => 'El dinero no puede ser negativo']);
  exit;
}

$sql = "UPDATE usuarios SET money = ? WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('di', $money, $user_id);
  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Dinero actualizado correctamente']);
  } else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar dinero']);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'error' => 'Error en la consulta']);
}

ob_end_flush();
