<?php
// API: Eliminar item del inventario de un jugador
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
$item_id = isset($input['item_id']) ? (int)$input['item_id'] : 0;

if ($user_id <= 0 || $item_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
  exit;
}

$sql = "DELETE FROM inventario WHERE user_id = ? AND item_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('ii', $user_id, $item_id);
  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item eliminado correctamente']);
  } else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar item']);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'error' => 'Error en la consulta']);
}

ob_end_flush();
