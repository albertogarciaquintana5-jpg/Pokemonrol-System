<?php
// API: Obtener datos completos de un jugador (equipo, caja, inventario)
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

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
  exit;
}

$response = ['success' => true];

// Datos del jugador
$sql = "SELECT id, nombre, apellido, correo, money FROM usuarios WHERE id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    $response['player'] = $row;
  } else {
    echo json_encode(['success' => false, 'error' => 'Jugador no encontrado']);
    exit;
  }
  $stmt->close();
}

// Equipo
$response['team'] = [];
$sql = "SELECT pb.*, ps.nombre AS especie, ps.sprite 
        FROM team t 
        LEFT JOIN pokemon_box pb ON t.pokemon_box_id = pb.id 
        LEFT JOIN pokemon_species ps ON pb.species_id = ps.id 
        WHERE t.user_id = ? AND pb.id IS NOT NULL
        ORDER BY t.slot ASC";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $response['team'][] = $row;
  }
  $stmt->close();
}

// Caja
$response['box'] = [];
$sql = "SELECT pb.*, ps.nombre AS especie, ps.sprite 
        FROM pokemon_box pb 
        JOIN pokemon_species ps ON pb.species_id = ps.id 
        WHERE pb.user_id = ? 
        ORDER BY pb.created_at DESC";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $response['box'][] = $row;
  }
  $stmt->close();
}

// Inventario
$response['inventory'] = [];
$sql = "SELECT i.cantidad, i.item_id, it.nombre, it.icono 
        FROM inventario i 
        JOIN items it ON it.id = i.item_id 
        WHERE i.user_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $response['inventory'][] = $row;
  }
  $stmt->close();
}

echo json_encode($response);
ob_end_flush();
