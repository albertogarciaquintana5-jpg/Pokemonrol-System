<?php
// API: Obtener opciones de evolucion para uno o varios Pokemon
ob_start();
header('Content-Type: application/json');
error_reporting(0);

session_start();
include '../db.php';
include 'admin_evolution_helpers.php';
ob_clean();

if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== 67) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

session_write_close();

$input = json_decode(file_get_contents('php://input'), true);
$pokemonIds = [];

if (is_array($input) && isset($input['pokemon_ids']) && is_array($input['pokemon_ids'])) {
    $pokemonIds = array_values(array_filter($input['pokemon_ids'], fn($id) => (int)$id > 0));
} elseif (is_array($input) && isset($input['pokemon_id'])) {
    $pokemonIds = [(int)$input['pokemon_id']];
} elseif (isset($_GET['pokemon_id'])) {
    $pokemonIds = [(int)$_GET['pokemon_id']];
}

$pokemonIds = array_values(array_unique(array_filter($pokemonIds, fn($id) => (int)$id > 0)));

if (count($pokemonIds) === 0) {
    echo json_encode(['success' => false, 'error' => 'No se proporcionaron Pokemon']);
    exit;
}

$speciesByPokemon = [];
$placeholders = implode(',', array_fill(0, count($pokemonIds), '?'));
$sql = "SELECT id, species_id FROM pokemon_box WHERE id IN ($placeholders)";
if ($stmt = $mysqli->prepare($sql)) {
    $types = str_repeat('i', count($pokemonIds));
    $stmt->bind_param($types, ...$pokemonIds);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $speciesByPokemon[(int)$row['id']] = (int)$row['species_id'];
    }
    $stmt->close();
}

if (count($pokemonIds) === 1) {
    $pokemonId = $pokemonIds[0];
    $speciesId = $speciesByPokemon[$pokemonId] ?? 0;
    $options = $speciesId > 0 ? admin_get_evolution_options_by_species($speciesId, $mysqli) : [];
    echo json_encode(['success' => true, 'options' => $options]);
    exit;
}

$results = [];
foreach ($pokemonIds as $pokemonId) {
    $speciesId = $speciesByPokemon[$pokemonId] ?? 0;
    $results[$pokemonId] = $speciesId > 0 ? admin_get_evolution_options_by_species($speciesId, $mysqli) : [];
}

echo json_encode(['success' => true, 'results' => $results]);
ob_end_flush();
