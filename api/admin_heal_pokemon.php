<?php
// API: Curar todos los Pokémon de un jugador (Centro Pokémon)
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

if ($user_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
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

// Curar todos los Pokémon del usuario (restaurar HP y quitar estados)
$sql = "UPDATE pokemon_box SET hp = max_hp, status = '' WHERE user_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Error al curar Pokémon']);
    exit;
  }
  $pokemon_curados = $stmt->affected_rows;
  $stmt->close();
}

echo json_encode([
  'success' => true, 
  'message' => 'Pokémon curados correctamente',
  'pokemon_curados' => $pokemon_curados
]);

ob_end_flush();
