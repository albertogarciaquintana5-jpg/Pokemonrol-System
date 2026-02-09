<?php
// API: Dar items a un jugador
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
$cantidad = isset($input['cantidad']) ? (int)$input['cantidad'] : 1;

if ($user_id <= 0 || $item_id <= 0 || $cantidad <= 0) {
  echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
  exit;
}

// Verificar que el usuario existe
$sql = "SELECT id FROM usuarios WHERE id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    exit;
  }
  $stmt->close();
}

// Verificar que el item existe
$sql = "SELECT id FROM items WHERE id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $item_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Item no encontrado']);
    exit;
  }
  $stmt->close();
}

// Insertar o actualizar inventario
$sql = "INSERT INTO inventario (user_id, item_id, cantidad) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('iiii', $user_id, $item_id, $cantidad, $cantidad);
  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item entregado correctamente']);
  } else {
    echo json_encode(['success' => false, 'error' => 'Error al dar item']);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'error' => 'Error en la consulta']);
}

ob_end_flush();
