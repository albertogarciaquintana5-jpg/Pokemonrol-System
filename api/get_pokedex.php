<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];

$sql = "SELECT p.species_id, ps.nombre, p.visto, p.capturado, p.veces_visto, p.first_seen_at FROM `pokedex` p JOIN `pokemon_species` ps ON p.species_id = ps.id WHERE p.user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) { $data[] = $row; }
$stmt->close();

echo json_encode(['success' => true, 'pokedex' => $data]);
exit;
?>