<?php
// API: Dar un Pokémon a un jugador
// Evitar cualquier salida antes del JSON
ob_start();

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', '0');

session_start();
include '../db.php';
include '../PokemonStatsCalculator.php';

// Limpiar buffer de salida para evitar HTML antes del JSON
ob_clean();

// Verificar que sea el admin
if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$species_id = isset($input['species_id']) ? (int)$input['species_id'] : 0;
$apodo = $input['apodo'] ?? null;
$nivel = isset($input['nivel']) ? (int)$input['nivel'] : 5;
$hp = isset($input['hp']) ? (int)$input['hp'] : 35;

if ($user_id <= 0 || $species_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
  exit;
}

if ($nivel < 1 || $nivel > 100) {
  echo json_encode(['success' => false, 'error' => 'El nivel debe estar entre 1 y 100']);
  exit;
}

if ($hp < 1) {
  echo json_encode(['success' => false, 'error' => 'El HP debe ser mayor a 0']);
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

// Verificar que la especie existe
$sql = "SELECT id FROM pokemon_species WHERE id = ? LIMIT 1";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('i', $species_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Especie no encontrada']);
    exit;
  }
  $stmt->close();
}

// Calcular stats basadas en el nivel y especie
$stats = PokemonStatsCalculator::calculateAllStats($species_id, $nivel, $mysqli);

if (!$stats) {
  echo json_encode(['success' => false, 'error' => 'Error al calcular estadísticas del Pokémon']);
  exit;
}

// Insertar Pokémon en la caja del jugador con stats calculadas
$status = '';
$apodo_value = $apodo ?? ''; // Convertir null a string vacío
$sql = "INSERT INTO pokemon_box (user_id, species_id, apodo, nivel, hp, max_hp, status, ataque, defensa, sp_ataque, sp_defensa, velocidad) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('iisiisiiiiii', 
    $user_id, $species_id, $apodo_value, $nivel, 
    $stats['max_hp'], $stats['max_hp'], $status,
    $stats['ataque'], $stats['defensa'], $stats['sp_ataque'], $stats['sp_defensa'], $stats['velocidad']
  );
  if ($stmt->execute()) {
    echo json_encode([
      'success' => true, 
      'message' => 'Pokémon entregado correctamente con stats calculadas', 
      'pokemon_id' => $mysqli->insert_id,
      'stats' => $stats
    ]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Error al insertar Pokémon: ' . $stmt->error]);
  }
  $stmt->close();
} else {
  echo json_encode(['success' => false, 'error' => 'Error en la consulta: ' . $mysqli->error]);
}

ob_end_flush();
