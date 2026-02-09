<?php
// API: Actualizar datos de un Pokémon (HP, nivel, exp, status)
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', '0');

session_start();
include '../db.php';
include '../PokemonStatsCalculator.php';
ob_clean();

// Verificar que sea el admin
if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$pokemon_id = isset($input['pokemon_id']) ? (int)$input['pokemon_id'] : 0;

if ($pokemon_id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID de Pokémon inválido']);
  exit;
}

// Actualizar Pokémon
$apodo = $input['apodo'] ?? null;
// Si apodo está vacío, poner null
if ($apodo === '') {
  $apodo = null;
}
$nivel = isset($input['nivel']) ? (int)$input['nivel'] : null;
$hp = isset($input['hp']) ? (int)$input['hp'] : null;
$max_hp = isset($input['max_hp']) ? (int)$input['max_hp'] : null;
$experiencia = isset($input['experiencia']) ? (int)$input['experiencia'] : null;
$status = $input['status'] ?? '';
// cp removido - la columna no existe en la tabla

// Validaciones de rango
if ($nivel !== null && ($nivel < 1 || $nivel > 100)) {
  echo json_encode(['success' => false, 'error' => 'El nivel debe estar entre 1 y 100']);
  exit;
}

if ($hp !== null && $hp < 0) {
  echo json_encode(['success' => false, 'error' => 'El HP no puede ser negativo']);
  exit;
}

if ($max_hp !== null && $max_hp < 1) {
  echo json_encode(['success' => false, 'error' => 'El HP máximo debe ser mayor a 0']);
  exit;
}

if ($experiencia !== null && $experiencia < 0) {
  echo json_encode(['success' => false, 'error' => 'La experiencia no puede ser negativa']);
  exit;
}

// Verificar si el nivel cambió para recalcular stats
$nivel_cambiado = false;
$nivel_anterior = null;

if ($nivel !== null) {
  $sql = "SELECT nivel FROM pokemon_box WHERE id = ? LIMIT 1";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('i', $pokemon_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $nivel_anterior = $row['nivel'];
    if ($nivel_anterior != $nivel) {
      $nivel_cambiado = true;
    }
  }
  $stmt->close();
}

// Si el nivel cambió, usar el sistema automático de stats
if ($nivel_cambiado) {
  $newStats = PokemonStatsCalculator::updatePokemonStats($pokemon_id, $nivel, $mysqli);
  
  if ($newStats === false) {
    echo json_encode(['success' => false, 'error' => 'Error al calcular estadísticas']);
    exit;
  }
  
  // Actualizar solo apodo, experiencia y status (las stats ya se actualizaron)
  $sql = "UPDATE pokemon_box SET apodo = ?, experiencia = ?, status = ? WHERE id = ?";
  if ($stmt = $mysqli->prepare($sql)) {
    $apodo_value = $apodo ?? '';
    $exp_value = $experiencia ?? 0;
    $stmt->bind_param('sisi', $apodo_value, $exp_value, $status, $pokemon_id);
    if (!$stmt->execute()) {
      echo json_encode(['success' => false, 'error' => 'Error al actualizar Pokémon: ' . $stmt->error]);
      ob_end_flush();
      exit;
    }
    $stmt->close();
  }
  
  $mensaje = "Pokémon actualizado correctamente. ¡Subió al nivel $nivel! Stats recalculadas automáticamente.";
  
} else {
  // No cambió el nivel, actualizar normalmente
  // Asegurar que los valores numéricos no sean null
  if ($nivel === null) $nivel = 1;
  if ($hp === null) $hp = 1;
  if ($max_hp === null) $max_hp = 100;
  if ($experiencia === null) $experiencia = 0;

  $sql = "UPDATE pokemon_box SET 
          apodo = ?, 
          nivel = ?, 
          hp = ?, 
          max_hp = ?, 
          experiencia = ?, 
          status = ?
          WHERE id = ?";

  if ($stmt = $mysqli->prepare($sql)) {
    // Bind con valores garantizados no-null
    $apodo_value = $apodo ?? '';
    $stmt->bind_param('siiiisi', $apodo_value, $nivel, $hp, $max_hp, $experiencia, $status, $pokemon_id);
    if (!$stmt->execute()) {
      echo json_encode(['success' => false, 'error' => 'Error al actualizar Pokémon: ' . $stmt->error]);
      ob_end_flush();
      exit;
    }
    $stmt->close();
  } else {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación: ' . $mysqli->error]);
    ob_end_flush();
    exit;
  }
  
  $mensaje = "Pokémon actualizado correctamente";
}

echo json_encode(['success' => true, 'message' => $mensaje, 'level_up' => $nivel_cambiado]);
ob_end_flush();
