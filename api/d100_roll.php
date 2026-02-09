<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'DB connection failed']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'No autenticado']); exit; }

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$value = isset($data['value']) ? (int)$data['value'] : null;
if ($value === null || $value < 1 || $value > 100) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Valor inválido']); exit; }

$user_id = (int)$_SESSION['user']['id'];
$stmt = $mysqli->prepare('INSERT INTO d100_rolls (user_id, value) VALUES (?, ?)');
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
$stmt->bind_param('ii', $user_id, $value);
if (!$stmt->execute()) { $stmt->close(); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Execute failed']); exit; }
$id = $mysqli->insert_id;
$stmt->close();

echo json_encode(['success'=>true,'id'=>$id,'value'=>$value,'created_at'=>date('c')]);
exit;
?>
