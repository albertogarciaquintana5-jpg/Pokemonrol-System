<?php
header('Content-Type: application/json');
session_start();
try { require_once __DIR__ . '/../db.php'; } catch (Exception $e) { http_response_code(500); echo json_encode(['error' => 'DB connection failed']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed']); exit; }
if (!isset($_SESSION['user']['id'])) { http_response_code(403); echo json_encode(['error' => 'Unauthorized']); exit; }
$user_id = (int)$_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'equip') {
    // Needs slot and box_id
    $slot = isset($input['slot']) ? (int)$input['slot'] : 0;
    $box_id = isset($input['box_id']) ? (int)$input['box_id'] : 0;
    if ($slot < 1 || $slot > 6 || $box_id <= 0) { http_response_code(400); echo json_encode(['error' => 'Invalid slot/box_id']); exit; }

    // Ensure the pokemon belongs to the user
    $sql = 'SELECT id FROM pokemon_box WHERE id = ? AND user_id = ? LIMIT 1';
    $stmt = $mysqli->prepare($sql); $stmt->bind_param('ii', $box_id, $user_id); $stmt->execute(); $res = $stmt->get_result(); if (!$res || $res->num_rows == 0) { $stmt->close(); http_response_code(400); echo json_encode(['error' => 'Pokemon no pertenece al usuario']); exit; } $stmt->close();

    // Transaction to safely auto-move and assign
    $mysqli->begin_transaction();
    try {
        // If this pokemon is already equipped in another slot, clear it
        $sql = 'SELECT slot FROM team WHERE user_id = ? AND pokemon_box_id = ? LIMIT 1';
        $stmt = $mysqli->prepare($sql); $stmt->bind_param('ii', $user_id, $box_id); $stmt->execute(); $res = $stmt->get_result(); $existing_slot_row = $res->fetch_assoc(); $stmt->close();
        $changed = [];
        if ($existing_slot_row && (int)$existing_slot_row['slot'] !== $slot) {
            $oldSlot = (int)$existing_slot_row['slot'];
            $sql = 'UPDATE team SET pokemon_box_id = NULL WHERE user_id = ? AND slot = ?';
            $stmt = $mysqli->prepare($sql); $stmt->bind_param('ii', $user_id, $oldSlot); $stmt->execute(); $stmt->close();
            $changed['unequipped_slot'] = $oldSlot;
        }

        // Now upsert for the requested slot (insert or update)
        $sql2 = 'INSERT INTO team (user_id, slot, pokemon_box_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE pokemon_box_id = VALUES(pokemon_box_id)';
        $stmt = $mysqli->prepare($sql2); $stmt->bind_param('iii', $user_id, $slot, $box_id);
        if (!$stmt->execute()) { throw new Exception('Execute failed (upsert)'); }
        $stmt->close();

        $mysqli->commit();
        // fetch updated team
        $teamSql = "SELECT t.slot, pb.id AS box_id, ps.nombre AS especie, pb.apodo, pb.nivel FROM team t LEFT JOIN pokemon_box pb ON t.pokemon_box_id = pb.id LEFT JOIN pokemon_species ps ON pb.species_id = ps.id WHERE t.user_id = ? ORDER BY t.slot ASC";
        $teamStmt = $mysqli->prepare($teamSql); $teamStmt->bind_param('i', $user_id); $teamStmt->execute(); $teamRes = $teamStmt->get_result(); $teamData = []; while ($row = $teamRes->fetch_assoc()) { $teamData[] = $row; } $teamStmt->close();
        $msg = 'Pokémon equipado.' . (isset($changed['unequipped_slot']) ? ' (Se ha desequipado el slot ' . $changed['unequipped_slot'] . ')' : '');
        echo json_encode(['success' => true, 'message' => $msg, 'team' => $teamData]); exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]); exit;
    }
}

if ($action === 'unequip') {
    $slot = isset($input['slot']) ? (int)$input['slot'] : 0;
    if ($slot < 1 || $slot > 6) { http_response_code(400); echo json_encode(['error' => 'Invalid slot']); exit; }
    $sql = 'UPDATE team SET pokemon_box_id = NULL WHERE user_id = ? AND slot = ?';
    $stmt = $mysqli->prepare($sql); $stmt->bind_param('ii', $user_id, $slot); if (!$stmt->execute()) { $stmt->close(); http_response_code(500); echo json_encode(['error' => 'Execute failed']); exit; } $stmt->close();
    // return updated team
    $teamSql = "SELECT t.slot, pb.id AS box_id, ps.nombre AS especie, pb.apodo, pb.nivel FROM team t LEFT JOIN pokemon_box pb ON t.pokemon_box_id = pb.id LEFT JOIN pokemon_species ps ON pb.species_id = ps.id WHERE t.user_id = ? ORDER BY t.slot ASC";
    $teamStmt = $mysqli->prepare($teamSql); $teamStmt->bind_param('i', $user_id); $teamStmt->execute(); $teamRes = $teamStmt->get_result(); $teamData = []; while ($row = $teamRes->fetch_assoc()) { $teamData[] = $row; } $teamStmt->close();
    echo json_encode(['success' => true, 'message' => 'Slot desequipado.', 'team' => $teamData]); exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
exit;
?>