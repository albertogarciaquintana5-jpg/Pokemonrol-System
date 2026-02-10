<?php
// API: Evolucionar un Pokemon
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', '0');

session_start();
include '../db.php';
include '../PokemonStatsCalculator.php';
include 'admin_evolution_helpers.php';
ob_clean();

if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

session_write_close();

$input = json_decode(file_get_contents('php://input'), true);
$pokemonId = isset($input['pokemon_id']) ? (int)$input['pokemon_id'] : 0;
$targetSpeciesId = isset($input['target_species_id']) ? (int)$input['target_species_id'] : 0;

if ($pokemonId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de Pokemon invalido']);
    exit;
}

$sql = "SELECT id, species_id, nivel, hp, max_hp FROM pokemon_box WHERE id = ? LIMIT 1";
$pokemon = null;
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('i', $pokemonId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $pokemon = $row;
    }
    $stmt->close();
}

if (!$pokemon) {
    echo json_encode(['success' => false, 'error' => 'Pokemon no encontrado']);
    exit;
}

$oldHp = isset($pokemon['hp']) ? (int)$pokemon['hp'] : null;
$oldMaxHp = isset($pokemon['max_hp']) ? (int)$pokemon['max_hp'] : null;
$hpRatio = null;
if ($oldHp !== null && $oldMaxHp !== null && $oldMaxHp > 0) {
    $hpRatio = $oldHp / $oldMaxHp;
}

$options = admin_get_evolution_options_by_species((int)$pokemon['species_id'], $mysqli);
if (count($options) === 0) {
    echo json_encode(['success' => false, 'error' => 'Este Pokemon no tiene evolucion disponible en la base de datos']);
    exit;
}

$validTargets = array_map(fn($opt) => (int)$opt['id'], $options);

if ($targetSpeciesId <= 0) {
    if (count($validTargets) !== 1) {
        echo json_encode(['success' => false, 'error' => 'Evolucion multiple: selecciona una opcion']);
        exit;
    }
    $targetSpeciesId = $validTargets[0];
}

if (!in_array($targetSpeciesId, $validTargets, true)) {
    echo json_encode(['success' => false, 'error' => 'Evolucion no valida']);
    exit;
}

$mysqli->begin_transaction();

$updateOk = false;
if ($stmt = $mysqli->prepare('UPDATE pokemon_box SET species_id = ? WHERE id = ?')) {
    $stmt->bind_param('ii', $targetSpeciesId, $pokemonId);
    $updateOk = $stmt->execute();
    $stmt->close();
}

if (!$updateOk) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'error' => 'Error al actualizar especie']);
    exit;
}

$recalcOk = false;
if ($stmt = $mysqli->prepare('CALL recalculate_pokemon_stats(?)')) {
    $stmt->bind_param('i', $pokemonId);
    if ($stmt->execute()) {
        $recalcOk = true;
    }
    $stmt->close();
    while ($mysqli->more_results() && $mysqli->next_result()) {
        // limpiar resultados del procedimiento
    }
}

if (!$recalcOk) {
    $nivel = isset($pokemon['nivel']) ? (int)$pokemon['nivel'] : 1;
    $recalcOk = PokemonStatsCalculator::updatePokemonStats($pokemonId, $nivel, $mysqli) !== false;
}

if (!$recalcOk) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'error' => 'Error al recalcular stats']);
    exit;
}

if ($hpRatio !== null) {
    $newMaxHp = null;
    if ($stmt = $mysqli->prepare('SELECT max_hp FROM pokemon_box WHERE id = ? LIMIT 1')) {
        $stmt->bind_param('i', $pokemonId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $newMaxHp = (int)$row['max_hp'];
        }
        $stmt->close();
    }

    if ($newMaxHp !== null && $newMaxHp > 0) {
        $newHp = (int)round($newMaxHp * $hpRatio);
        if ($newHp < 0) $newHp = 0;
        if ($newHp > $newMaxHp) $newHp = $newMaxHp;

        if ($stmt = $mysqli->prepare('UPDATE pokemon_box SET hp = ? WHERE id = ?')) {
            $stmt->bind_param('ii', $newHp, $pokemonId);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$mysqli->commit();

echo json_encode(['success' => true, 'message' => 'Pokemon evolucionado correctamente']);
ob_end_flush();
