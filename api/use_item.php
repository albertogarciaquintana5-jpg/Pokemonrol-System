<?php
header('Content-Type: application/json');
session_start();
try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = (int)$_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$item_id = isset($input['item_id']) ? (int)$input['item_id'] : 0;
$box_id = isset($input['box_id']) ? (int)$input['box_id'] : null; // optional target pokemon id

// Get item metadata (clave/nombre) to optionally apply effects
$itemInfo = null;
$stmtItem = $mysqli->prepare('SELECT id, clave, nombre, effect_type, effect_value FROM items WHERE id = ? LIMIT 1');
if ($stmtItem) { $stmtItem->bind_param('i', $item_id); $stmtItem->execute(); $itemRes = $stmtItem->get_result(); $itemInfo = $itemRes->fetch_assoc(); $stmtItem->close(); }

if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid item_id']);
    exit;
}

// Check quantity
$sql = 'SELECT cantidad FROM inventario WHERE user_id = ? AND item_id = ? LIMIT 1';
if (!$stmt = $mysqli->prepare($sql)) { http_response_code(500); echo json_encode(['error' => 'Prepare failed']); exit; }
$stmt->bind_param('ii', $user_id, $item_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || (int)$row['cantidad'] <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No hay unidades suficientes']);
    exit;
}

// decrement quantity atomically

// Use a transaction to ensure atomicity
$mysqli->begin_transaction();
try {
    $upd = 'UPDATE inventario SET cantidad = cantidad - 1 WHERE user_id = ? AND item_id = ? AND cantidad > 0';
    if (!$stmt = $mysqli->prepare($upd)) { throw new Exception('Prepare failed'); }
    $stmt->bind_param('ii', $user_id, $item_id);
    if (!$stmt->execute()) { $stmt->close(); throw new Exception('Execute failed'); }
    $stmt->close();
    // If this is a potion and we have a target box_id, apply heal effect inside the same transaction
    $applied = null;
    // Helper to check if a column exists in the current database
    $column_exists = function($table, $column) use ($mysqli) {
        $cstmt = $mysqli->prepare('SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        if (!$cstmt) return false;
        $cstmt->bind_param('ss', $table, $column);
        $cstmt->execute();
        $cres = $cstmt->get_result();
        $c = $cres->fetch_assoc();
        $cstmt->close();
        return ((int)$c['c']) > 0;
    };

    // Apply item effect if defined and target provided
    if ($itemInfo && $box_id && !empty($itemInfo['effect_type'])) {
        // Validate pokemon belongs to user and fetch fields we might need
        $selectCols = 'id, hp';
        if ($column_exists('pokemon_box', 'max_hp')) $selectCols .= ', max_hp';
        if ($column_exists('pokemon_box', 'status')) $selectCols .= ', status';
        $qsql = "SELECT $selectCols FROM pokemon_box WHERE id = ? AND user_id = ? LIMIT 1";
        $q = $mysqli->prepare($qsql);
        if ($q) { $q->bind_param('ii', $box_id, $user_id); $q->execute(); $qres = $q->get_result(); $pokemon = $qres->fetch_assoc(); $q->close(); }
        if (!empty($pokemon)) {
            $effectType = $itemInfo['effect_type'];
            $effectValue = is_null($itemInfo['effect_value']) ? null : $itemInfo['effect_value'];
            $currentHp = (int)($pokemon['hp'] ?? 0);
            $maxHp = isset($pokemon['max_hp']) ? (int)$pokemon['max_hp'] : null;
            $status = isset($pokemon['status']) ? $pokemon['status'] : null;
            $applied = ['box_id' => $box_id, 'effect' => $effectType];

            switch ($effectType) {
                case 'heal_flat':
                    $heal = ($effectValue !== null) ? (int)$effectValue : 20;
                    $newHp = $currentHp + $heal;
                    if ($maxHp !== null) $newHp = min($newHp, $maxHp);
                    $up = $mysqli->prepare('UPDATE pokemon_box SET hp = ? WHERE id = ? AND user_id = ?');
                    if ($up) { $up->bind_param('iii', $newHp, $box_id, $user_id); $up->execute(); $up->close(); }
                    $applied['healed'] = $heal;
                    $applied['new_hp'] = $newHp;
                    break;
                case 'heal_percent':
                    // Prefer max_hp if available; otherwise, percent of current HP
                    if ($maxHp !== null && $effectValue !== null) {
                        $heal = (int)round($maxHp * ((float)$effectValue / 100.0));
                    } elseif ($effectValue !== null) {
                        $heal = (int)round($currentHp * ((float)$effectValue / 100.0));
                    } else {
                        $heal = 20;
                    }
                    $newHp = $currentHp + $heal;
                    if ($maxHp !== null) $newHp = min($newHp, $maxHp);
                    $up = $mysqli->prepare('UPDATE pokemon_box SET hp = ? WHERE id = ? AND user_id = ?');
                    if ($up) { $up->bind_param('iii', $newHp, $box_id, $user_id); $up->execute(); $up->close(); }
                    $applied['healed'] = $heal;
                    $applied['new_hp'] = $newHp;
                    break;
                case 'revive':
                    // If pokemon has hp > 0, nothing to do; otherwise set to effectValue or maxHp or 1
                    if ($currentHp <= 0) {
                        if ($effectValue !== null) {
                            $newHp = (int)$effectValue;
                        } elseif ($maxHp !== null) {
                            $newHp = $maxHp;
                        } else {
                            $newHp = 50; // fallback
                        }
                        $up = $mysqli->prepare('UPDATE pokemon_box SET hp = ? WHERE id = ? AND user_id = ?');
                        if ($up) { $up->bind_param('iii', $newHp, $box_id, $user_id); $up->execute(); $up->close(); }
                        $applied['revived_to'] = $newHp;
                    } else {
                        $applied['note'] = 'Pokemon ya con vida';
                    }
                    break;
                case 'clear_status':
                    if ($column_exists('pokemon_box', 'status')) {
                        $newStatus = '';
                        $up = $mysqli->prepare('UPDATE pokemon_box SET status = ? WHERE id = ? AND user_id = ?');
                        if ($up) { $up->bind_param('sii', $newStatus, $box_id, $user_id); $up->execute(); $up->close(); }
                        $applied['cleared_status'] = true;
                    } else {
                        $applied['cleared_status'] = false;
                        $applied['note'] = 'No existe columna status';
                    }
                    break;
                default:
                    $applied['note'] = 'Efecto no soportado: ' . $effectType;
            }
        }
    }
    // Remove inventory rows that reached zero to keep table clean
    $del = $mysqli->prepare('DELETE FROM inventario WHERE user_id = ? AND item_id = ? AND cantidad <= 0');
    if ($del) { $del->bind_param('ii', $user_id, $item_id); $del->execute(); $del->close(); }

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500); echo json_encode(['error' => $e->getMessage()]); exit;
}

// Return updated quantity
// Return updated inventory for the user
$columnExistsStmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
$hasEffectCols = false;
if ($columnExistsStmt) {
    $t = 'items'; $c = 'effect_type'; $columnExistsStmt->bind_param('ss', $t, $c); $columnExistsStmt->execute(); $cres = $columnExistsStmt->get_result(); $has = $cres->fetch_assoc(); if ($has && (int)$has['c'] > 0) $hasEffectCols = true;
    $columnExistsStmt->close();
}
if ($hasEffectCols) {
    $sqlInv = 'SELECT i.item_id, i.cantidad, it.nombre, it.clave, it.effect_type, it.effect_value FROM inventario i JOIN items it ON it.id=i.item_id WHERE i.user_id = ?';
} else {
    $sqlInv = 'SELECT i.item_id, i.cantidad, it.nombre, it.clave FROM inventario i JOIN items it ON it.id=i.item_id WHERE i.user_id = ?';
}
$stmt = $mysqli->prepare($sqlInv); $stmt->bind_param('i', $user_id); $stmt->execute(); $rInv = $stmt->get_result(); $invData = []; while ($r = $rInv->fetch_assoc()) $invData[] = $r; $stmt->close();

$remaining = 0; foreach ($invData as $ii) if ((int)$ii['item_id'] === $item_id) $remaining = (int)$ii['cantidad'];

$ret = ['success' => true, 'remaining' => $remaining, 'inventory' => $invData, 'message' => 'Item usado'];
if (!empty($applied)) { $ret['applied'] = $applied; $ret['message'] = 'Item aplicado: +' . ($applied['healed'] ?? 0) . ' HP'; }
echo json_encode($ret);
exit;
?>