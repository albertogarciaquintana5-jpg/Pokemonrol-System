<?php
// API: Obtener datos de un Pokémon específico con sus movimientos
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

$pokemon_id = isset($_GET['pokemon_id']) ? (int)$_GET['pokemon_id'] : 0;

if ($pokemon_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de Pokémon inválido']);
  exit;
}

$response = ['success' => true];

// Datos del Pokémon
$sql = "SELECT pb.*, ps.nombre AS especie, ps.sprite 
        FROM pokemon_box pb 
        JOIN pokemon_species ps ON pb.species_id = ps.id 
        WHERE pb.id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $pokemon_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    $response['pokemon'] = $row;
  } else {
    echo json_encode(['success' => false, 'error' => 'Pokémon no encontrado']);
    exit;
  }
  $stmt->close();
}

// Movimientos
$response['moves'] = [];
$sql = "SELECT pm.*, m.nombre, m.categoria, m.potencia
        FROM pokemon_movimiento pm 
        JOIN movimientos m ON pm.movimiento_id = m.id 
        WHERE pm.pokemon_box_id = ?
        ORDER BY pm.slot ASC";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $pokemon_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $response['moves'][] = $row;
  }
  $stmt->close();
}

echo json_encode($response);
ob_end_flush();
