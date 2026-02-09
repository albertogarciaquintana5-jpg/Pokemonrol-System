<?php
/**
 * d100_clear.php
 * Borra el historial de tiradas de dado del usuario autenticado
 */
header('Content-Type: application/json; charset=utf-8');
session_start();

try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

// Borrar todos los registros de dados del usuario
$stmt = $mysqli->prepare('DELETE FROM d100_rolls WHERE user_id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('i', $user_id);
$success = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

if ($success) {
    echo json_encode([
        'success' => true, 
        'message' => 'Historial borrado correctamente',
        'deleted_count' => $affected_rows
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al borrar historial']);
}

exit;
?>
