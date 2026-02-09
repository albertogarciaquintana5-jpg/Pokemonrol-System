<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];

$colStmt = $mysqli->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
$cols = ['t.slot', 'pb.id AS box_id', 'ps.nombre AS especie', 'ps.sprite AS sprite', 'pb.apodo', 'pb.nivel'];
$optCols = [];
if ($colStmt) {
	$tbl = 'pokemon_box';
	$colStmt->bind_param('s', $tbl);
	$colStmt->execute();
	$cres = $colStmt->get_result();
	while ($c = $cres->fetch_assoc()) { $optCols[$c['COLUMN_NAME']] = true; }
	$colStmt->close();
}
if (isset($optCols['cp'])) $cols[] = 'pb.cp';
if (isset($optCols['hp'])) $cols[] = 'pb.hp';
if (isset($optCols['max_hp'])) $cols[] = 'pb.max_hp';
if (isset($optCols['status'])) $cols[] = 'pb.status';

$sql = 'SELECT ' . implode(', ', $cols) . ' FROM team t LEFT JOIN pokemon_box pb ON t.pokemon_box_id = pb.id LEFT JOIN pokemon_species ps ON pb.species_id = ps.id WHERE t.user_id = ? ORDER BY t.slot ASC';
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$team = [];
while ($row = $res->fetch_assoc()) { $team[] = $row; }
$stmt->close();

echo json_encode(['success' => true, 'team' => $team]);
exit;
?>