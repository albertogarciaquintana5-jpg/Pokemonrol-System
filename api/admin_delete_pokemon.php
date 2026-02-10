<?php
// API: Borrar un Pokemon de un jugador
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
$pokemon_id = isset($input['pokemon_id']) ? (int)$input['pokemon_id'] : 0;

if ($pokemon_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de Pokemon invalido']);
  exit;
}

// Verificar que existe
$user_id = null;
$sql = "SELECT user_id FROM pokemon_box WHERE id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $pokemon_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    $user_id = (int)$row['user_id'];
  }
  $stmt->close();
}

if (!$user_id) {
  echo json_encode(['success' => false, 'error' => 'Pokemon no encontrado']);
  exit;
}

$mysqli->begin_transaction();
try {
  // Quitar de equipo
  if ($stmt = $mysqli->prepare('DELETE FROM team WHERE pokemon_box_id = ?')) {
    $stmt->bind_param('i', $pokemon_id);
    if (!$stmt->execute()) {
      throw new Exception('Error al limpiar equipo');
    }
    $stmt->close();
  } else {
    throw new Exception('Error en la consulta de equipo');
  }

  // Quitar movimientos
  if ($stmt = $mysqli->prepare('DELETE FROM pokemon_movimiento WHERE pokemon_box_id = ?')) {
    $stmt->bind_param('i', $pokemon_id);
    if (!$stmt->execute()) {
      throw new Exception('Error al borrar movimientos');
    }
    $stmt->close();
  } else {
    throw new Exception('Error en la consulta de movimientos');
  }

  // Borrar de caja
  if ($stmt = $mysqli->prepare('DELETE FROM pokemon_box WHERE id = ? LIMIT 1')) {
    $stmt->bind_param('i', $pokemon_id);
    if (!$stmt->execute()) {
      throw new Exception('Error al borrar Pokemon');
    }
    $stmt->close();
  } else {
    throw new Exception('Error en la consulta de caja');
  }

  $mysqli->commit();
  echo json_encode(['success' => true, 'message' => 'Pokemon borrado']);
} catch (Exception $e) {
  $mysqli->rollback();
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();
