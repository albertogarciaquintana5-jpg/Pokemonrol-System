<?php
include '../db.php';
include '../helpers.php'; // Helper functions
session_start();

header('Content-Type: application/json');

// Verificar que el usuario esté logueado y sea el admin (ID 67)
if (!isset($_SESSION['user'])) {
  echo json_encode(['error' => 'No autorizado - Sin sesión']);
  exit;
}

$user_id = (int)($_SESSION['user']['id'] ?? 0);
if ($user_id !== 67) {
  echo json_encode(['error' => 'No autorizado - Solo el admin (ID 67) puede usar esta función', 'tu_id' => $user_id]);
  exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
  echo json_encode(['error' => 'Query vacío']);
  exit;
}

$results = [];
$searchPattern = '%' . $query . '%';

// Usar COLLATE utf8mb4_general_ci para búsqueda sin distinción de mayúsculas/minúsculas
$sql = "SELECT id, nombre, sprite FROM pokemon_species WHERE nombre COLLATE utf8mb4_general_ci LIKE ? ORDER BY nombre LIMIT 20";

if ($stmt = $mysqli->prepare($sql)) {
  $stmt->bind_param('s', $searchPattern);
  if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error ejecutando consulta', 'sql_error' => $stmt->error]);
    exit;
  }
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) {
    add_sprite_url($r, 'sprite', __DIR__ . '/../img/pokemon/');
    $results[] = $r;
  }
  $stmt->close();
} else {
  echo json_encode(['error' => 'Error preparando consulta SQL', 'sql_error' => $mysqli->error]);
  exit;
}

// Log de debug (solo en desarrollo)
error_log("Búsqueda de especies: query='$query', resultados=" . count($results));

echo json_encode($results);
