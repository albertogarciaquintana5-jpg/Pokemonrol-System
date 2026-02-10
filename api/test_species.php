<?php
include '../db.php';
session_start();

header('Content-Type: application/json');

// Script de prueba para verificar datos de especies
$test = [];

// Contar total de especies
$count_sql = "SELECT COUNT(*) as total FROM pokemon_species";
$count_result = $mysqli->query($count_sql);
if ($count_result) {
  $row = $count_result->fetch_assoc();
  $test['total_especies'] = $row['total'];
}

// Obtener primeras 10 especies
$test['primeras_10'] = [];
$sample_sql = "SELECT id, nombre, sprite FROM pokemon_species ORDER BY id LIMIT 10";
$sample_result = $mysqli->query($sample_sql);
if ($sample_result) {
  while ($row = $sample_result->fetch_assoc()) {
    $test['primeras_10'][] = $row;
  }
}

// Buscar "pikachu" específicamente
$test['busqueda_pikachu'] = [];
$pika_sql = "SELECT id, nombre, sprite FROM pokemon_species WHERE nombre LIKE '%pikachu%' COLLATE utf8mb4_general_ci";
$pika_result = $mysqli->query($pika_sql);
if ($pika_result) {
  while ($row = $pika_result->fetch_assoc()) {
    $test['busqueda_pikachu'][] = $row;
  }
}

// Info de la sesión
$test['sesion'] = [
  'user_exists' => isset($_SESSION['user']),
  'rol' => $_SESSION['user']['rol'] ?? 'no definido',
  'nombre' => $_SESSION['user']['nombre'] ?? 'no definido'
];

echo json_encode($test, JSON_PRETTY_PRINT);
