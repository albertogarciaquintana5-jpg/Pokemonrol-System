<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];

// Build select dynamically to include optional columns if they exist (max_hp, status)
$colStmt = $mysqli->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
$cols = ['pb.*'];
$optCols = [];
if ($colStmt) {
	$tbl = 'pokemon_box';
	$colStmt->bind_param('s', $tbl);
	$colStmt->execute();
	$cres = $colStmt->get_result();
	while ($c = $cres->fetch_assoc()) { $optCols[$c['COLUMN_NAME']] = true; }
	$colStmt->close();
}
if (isset($optCols['max_hp'])) $cols[] = 'pb.max_hp';
if (isset($optCols['status'])) $cols[] = 'pb.status';

$sql = 'SELECT ' . implode(', ', $cols) . ', ps.nombre AS especie, ps.sprite AS sprite FROM pokemon_box pb JOIN pokemon_species ps ON ps.id = pb.species_id WHERE pb.user_id = ? ORDER BY pb.created_at DESC';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$box = [];
while ($row = $res->fetch_assoc()) { $box[] = $row; }
$stmt->close();

echo json_encode(['success' => true, 'box' => $box]);
exit;
?>