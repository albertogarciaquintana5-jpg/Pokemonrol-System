<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'DB connection failed']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'No autenticado']); exit; }

$user_id = (int)$_SESSION['user']['id'];
$limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 50;
$stmt = $mysqli->prepare('SELECT id, value, created_at FROM d100_rolls WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
$stmt->bind_param('ii', $user_id, $limit);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
$stmt->close();

echo json_encode(['success'=>true,'rolls'=>$rows]);
exit;
?>
