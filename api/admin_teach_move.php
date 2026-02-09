<?php
// API: Enseñar movimiento a un Pokémon con validación de nivel
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', '0');

session_start();
include '../db.php';
ob_clean();

// Verificar que sea el admin
if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$pokemon_box_id = isset($input['pokemon_box_id']) ? (int)$input['pokemon_box_id'] : 0;
$movimiento_id = isset($input['movimiento_id']) ? (int)$input['movimiento_id'] : 0;

// Validaciones básicas
if ($pokemon_box_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de Pokémon inválido']);
    exit;
}

if ($movimiento_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de movimiento inválido']);
    exit;
}

// Obtener nivel del Pokémon
$sql = "SELECT nivel, species_id FROM pokemon_box WHERE id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $pokemon_box_id);
$stmt->execute();
$result = $stmt->get_result();

if ($pokemon = $result->fetch_assoc()) {
    $nivel_pokemon = (int)$pokemon['nivel'];
    $stmt->close();
    
    // Obtener información del movimiento
    $sql = "SELECT nombre, nivel_requerido FROM movimientos WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $movimiento_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($movimiento = $result->fetch_assoc()) {
        $nivel_requerido = (int)$movimiento['nivel_requerido'];
        $nombre_movimiento = $movimiento['nombre'];
        $stmt->close();
        
        // VALIDAR NIVEL
        if ($nivel_pokemon < $nivel_requerido) {
            echo json_encode([
                'success' => false, 
                'error' => "El Pokémon necesita nivel $nivel_requerido para aprender $nombre_movimiento (nivel actual: $nivel_pokemon)",
                'nivel_requerido' => $nivel_requerido,
                'nivel_actual' => $nivel_pokemon
            ]);
            ob_end_flush();
            exit;
        }
        
        // Verificar si ya conoce el movimiento
        $sql = "SELECT id FROM pokemon_movimiento WHERE pokemon_box_id = ? AND movimiento_id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $pokemon_box_id, $movimiento_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'El Pokémon ya conoce este movimiento']);
            $stmt->close();
            ob_end_flush();
            exit;
        }
        $stmt->close();
        
        // Verificar cuántos movimientos tiene (máximo 4)
        $sql = "SELECT COUNT(*) as total FROM pokemon_movimiento WHERE pokemon_box_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $pokemon_box_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_movimientos = (int)$row['total'];
        $stmt->close();
        
        if ($total_movimientos >= 4) {
            echo json_encode(['success' => false, 'error' => 'El Pokémon ya tiene 4 movimientos (máximo permitido)']);
            ob_end_flush();
            exit;
        }
        
        // Enseñar el movimiento
        $sql = "INSERT INTO pokemon_movimiento (pokemon_box_id, movimiento_id, slot) VALUES (?, ?, 1)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $pokemon_box_id, $movimiento_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "¡$nombre_movimiento aprendido correctamente!",
                'movimiento' => $nombre_movimiento,
                'movimientos_totales' => $total_movimientos + 1
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al enseñar el movimiento: ' . $stmt->error]);
        }
        $stmt->close();
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Movimiento no encontrado']);
        $stmt->close();
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'Pokémon no encontrado']);
    $stmt->close();
}

ob_end_flush();
