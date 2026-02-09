<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$species_id = isset($input['species_id']) ? (int)$input['species_id'] : 0;
$capturado = isset($input['capturado']) ? (int)$input['capturado'] : 0; // 0/1
$visto = isset($input['visto']) ? (int)$input['visto'] : 1; // default mark as visto
if ($species_id <= 0) { http_response_code(400); echo json_encode(['error'=>'Invalid species']); exit; }

// Upsert into pokedex
$stmt = $mysqli->prepare('INSERT INTO pokedex (user_id, species_id, visto, capturado, veces_visto, first_seen_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE visto = GREATEST(visto, VALUES(visto)), capturado = GREATEST(capturado, VALUES(capturado)), veces_visto = veces_visto + VALUES(veces_visto)');
$vistas = $visto ? 1 : 0; $capt = $capturado ? 1 : 0;
$stmt->bind_param('iiiii', $user_id, $species_id, $vistas, $capt, $vistas);
if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error'=>'execute failed']); exit; }
$stmt->close();

// Devolver información actualizada de la especie marcada
$info_stmt = $mysqli->prepare('SELECT p.visto, p.capturado, ps.nombre, ps.sprite FROM pokedex p JOIN pokemon_species ps ON p.species_id = ps.id WHERE p.user_id = ? AND p.species_id = ? LIMIT 1');
if ($info_stmt) {
    $info_stmt->bind_param('ii', $user_id, $species_id);
    $info_stmt->execute();
    $info_res = $info_stmt->get_result();
    $info = $info_res->fetch_assoc();
    $info_stmt->close();
    echo json_encode(['success'=>true, 'message'=>'Pokedex actualizada', 'entry' => $info]);
    exit;
}

echo json_encode(['success'=>true, 'message'=>'Pokedex actualizada']);
exit;
?>
