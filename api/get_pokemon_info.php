<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
try {
    require_once __DIR__ . '/../db.php';
    require_once __DIR__ . '/../helpers.php'; // Helper functions
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
$pokemon_box_id = isset($_GET['box_id']) ? (int)$_GET['box_id'] : 0;

if ($pokemon_box_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid box_id']);
    exit;
}

// ============================================
// INFORMACIÓN BÁSICA DEL POKÉMON
// ============================================
$sql = "SELECT 
            pb.id,
            pb.apodo,
            pb.nivel,
            pb.hp,
            pb.max_hp,
            pb.status,
            pb.ataque,
            pb.defensa,
            pb.sp_ataque,
            pb.sp_defensa,
            pb.velocidad,
            pb.iv_hp,
            pb.iv_ataque,
            pb.iv_defensa,
            pb.iv_sp_ataque,
            pb.iv_sp_defensa,
            pb.iv_velocidad,
            ps.id AS species_id,
            ps.nombre AS nombre_especie,
            ps.sprite,
            ps.hp AS base_hp,
            ps.ataque AS base_ataque,
            ps.defensa AS base_defensa,
            ps.sp_ataque AS base_sp_ataque,
            ps.sp_defensa AS base_sp_defensa,
            ps.velocidad AS base_velocidad,
            n.nombre AS naturaleza,
            n.stat_aumentado,
            n.stat_reducido,
            h.nombre AS habilidad,
            h.descripcion AS habilidad_desc,
            t1.id AS tipo_primario_id,
            t1.nombre AS tipo_primario,
            t1.color AS tipo_primario_color,
            t2.id AS tipo_secundario_id,
            t2.nombre AS tipo_secundario,
            t2.color AS tipo_secundario_color
        FROM pokemon_box pb
        JOIN pokemon_species ps ON pb.species_id = ps.id
        LEFT JOIN naturalezas n ON pb.naturaleza_id = n.id
        LEFT JOIN habilidades h ON pb.habilidad_id = h.id
        LEFT JOIN tipos t1 ON ps.tipo_primario_id = t1.id
        LEFT JOIN tipos t2 ON ps.tipo_secundario_id = t2.id
        WHERE pb.id = ? AND pb.user_id = ?
        LIMIT 1";

if (!$stmt = $mysqli->prepare($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param('ii', $pokemon_box_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    $stmt->close();
    http_response_code(404);
    echo json_encode(['error' => 'Pokémon no encontrado']);
    exit;
}

$pokemon = $res->fetch_assoc();
$stmt->close();

// Añadir sprite_url usando helper centralizado
add_sprite_url($pokemon, 'sprite', __DIR__ . '/../img/pokemon/');

// ============================================
// CALCULAR STATS FINALES (con modificadores de naturaleza)
// ============================================
function calcular_stat($base, $nivel, $iv = 31, $ev = 0, $modificador = 1.0) {
    return floor(((2 * $base + $iv + ($ev / 4)) * $nivel / 100 + 5) * $modificador);
}

// Fórmula correcta para HP (diferente del resto de stats)
function calcular_hp($base, $nivel, $iv = 31, $ev = 0) {
    return floor(((2 * $base + $iv + ($ev / 4)) * $nivel / 100) + $nivel + 10);
}

$nivel = (int)$pokemon['nivel'];
$modificador_aumentado = 1.1;   // 10% boost
$modificador_reducido = 0.9;    // 10% reduction

// Determinar modificadores según naturaleza
$mod_hp = 1.0;
$mod_ataque = 1.0;
$mod_defensa = 1.0;
$mod_sp_ataque = 1.0;
$mod_sp_defensa = 1.0;
$mod_velocidad = 1.0;

if (!empty($pokemon['stat_aumentado'])) {
    switch ($pokemon['stat_aumentado']) {
        case 'ataque': $mod_ataque = $modificador_aumentado; break;
        case 'defensa': $mod_defensa = $modificador_aumentado; break;
        case 'sp_ataque': $mod_sp_ataque = $modificador_aumentado; break;
        case 'sp_defensa': $mod_sp_defensa = $modificador_aumentado; break;
        case 'velocidad': $mod_velocidad = $modificador_aumentado; break;
    }
}

if (!empty($pokemon['stat_reducido'])) {
    switch ($pokemon['stat_reducido']) {
        case 'ataque': $mod_ataque = $modificador_reducido; break;
        case 'defensa': $mod_defensa = $modificador_reducido; break;
        case 'sp_ataque': $mod_sp_ataque = $modificador_reducido; break;
        case 'sp_defensa': $mod_sp_defensa = $modificador_reducido; break;
        case 'velocidad': $mod_velocidad = $modificador_reducido; break;
    }
}

// ============================================
// OBTENER STATS REALES DEL POKÉMON
// ============================================
// PRIORIDAD: 
// 1. Usar stats almacenadas en la BD (valores reales del Pokémon)
// 2. Si no existen, calcularlas usando IVs reales
// 3. Si no hay IVs, usar IV=31 (perfecto) como fallback
$stats = [];

// HP: usar max_hp de la BD si existe, si no calcular
if (!empty($pokemon['max_hp'])) {
    $stats['hp'] = (int)$pokemon['max_hp'];
} else {
    $iv_hp = isset($pokemon['iv_hp']) ? (int)$pokemon['iv_hp'] : 31;
    $stats['hp'] = calcular_hp((int)$pokemon['base_hp'], $nivel, $iv_hp);
}

// Ataque: usar ataque de BD si existe, si no calcular
if (!empty($pokemon['ataque'])) {
    $stats['ataque'] = (int)$pokemon['ataque'];
} else {
    $iv_ataque = isset($pokemon['iv_ataque']) ? (int)$pokemon['iv_ataque'] : 31;
    $stats['ataque'] = floor(calcular_stat((int)$pokemon['base_ataque'], $nivel, $iv_ataque) * $mod_ataque);
}

// Defensa
if (!empty($pokemon['defensa'])) {
    $stats['defensa'] = (int)$pokemon['defensa'];
} else {
    $iv_defensa = isset($pokemon['iv_defensa']) ? (int)$pokemon['iv_defensa'] : 31;
    $stats['defensa'] = floor(calcular_stat((int)$pokemon['base_defensa'], $nivel, $iv_defensa) * $mod_defensa);
}

// Sp. Ataque
if (!empty($pokemon['sp_ataque'])) {
    $stats['sp_ataque'] = (int)$pokemon['sp_ataque'];
} else {
    $iv_sp_ataque = isset($pokemon['iv_sp_ataque']) ? (int)$pokemon['iv_sp_ataque'] : 31;
    $stats['sp_ataque'] = floor(calcular_stat((int)$pokemon['base_sp_ataque'], $nivel, $iv_sp_ataque) * $mod_sp_ataque);
}

// Sp. Defensa
if (!empty($pokemon['sp_defensa'])) {
    $stats['sp_defensa'] = (int)$pokemon['sp_defensa'];
} else {
    $iv_sp_defensa = isset($pokemon['iv_sp_defensa']) ? (int)$pokemon['iv_sp_defensa'] : 31;
    $stats['sp_defensa'] = floor(calcular_stat((int)$pokemon['base_sp_defensa'], $nivel, $iv_sp_defensa) * $mod_sp_defensa);
}

// Velocidad
if (!empty($pokemon['velocidad'])) {
    $stats['velocidad'] = (int)$pokemon['velocidad'];
} else {
    $iv_velocidad = isset($pokemon['iv_velocidad']) ? (int)$pokemon['iv_velocidad'] : 31;
    $stats['velocidad'] = floor(calcular_stat((int)$pokemon['base_velocidad'], $nivel, $iv_velocidad) * $mod_velocidad);
}

// ============================================
// OBTENER MOVIMIENTOS
// ============================================
$movimientos_sql = "SELECT 
                        pm.slot,
                        m.id AS movimiento_id,
                        m.nombre,
                        m.potencia,
                        m.precision,
                        m.categoria,
                        m.descripcion,
                        t.nombre AS tipo,
                        t.color AS tipo_color
                    FROM pokemon_movimiento pm
                    JOIN movimientos m ON pm.movimiento_id = m.id
                    LEFT JOIN tipos t ON m.tipo_id = t.id
                    WHERE pm.pokemon_box_id = ?
                    ORDER BY pm.slot ASC";

$movimientos = [];
if ($mov_stmt = $mysqli->prepare($movimientos_sql)) {
    $mov_stmt->bind_param('i', $pokemon_box_id);
    $mov_stmt->execute();
    $mov_res = $mov_stmt->get_result();
    while ($row = $mov_res->fetch_assoc()) {
        $movimientos[] = $row;
    }
    $mov_stmt->close();
}

// ============================================
// OBTENER MOVIMIENTOS DISPONIBLES PARA APRENDER
// ============================================
$available_sql = "SELECT 
                    m.id,
                    m.nombre,
                    m.potencia,
                    m.precision,
                    m.categoria,
                    m.descripcion,
                    t.nombre AS tipo,
                    t.color AS tipo_color,
                    psm.nivel AS nivel_aprendizaje
                FROM pokemon_species_movimiento psm
                JOIN movimientos m ON psm.movimiento_id = m.id
                LEFT JOIN tipos t ON m.tipo_id = t.id
                WHERE psm.species_id = ?
                ORDER BY psm.nivel ASC";

$movimientos_disponibles = [];
if ($avail_stmt = $mysqli->prepare($available_sql)) {
    $avail_stmt->bind_param('i', $pokemon['species_id']);
    $avail_stmt->execute();
    $avail_res = $avail_stmt->get_result();
    while ($row = $avail_res->fetch_assoc()) {
        $movimientos_disponibles[] = $row;
    }
    $avail_stmt->close();
}

// ============================================
// COMPILAR RESPUESTA
// ============================================
$response = [
    'success' => true,
    'pokemon' => [
        'id' => (int)$pokemon['id'],
        'apodo' => $pokemon['apodo'],
        'nombre_especie' => $pokemon['nombre_especie'],
        'species_id' => (int)$pokemon['species_id'],
        'sprite' => $pokemon['sprite'],
        'sprite_url' => $pokemon['sprite_url'],
        'nivel' => $nivel,
        'hp_actual' => (int)$pokemon['hp'],
        'hp_maximo' => (int)($pokemon['max_hp'] ?? $stats['hp']),
        'status' => $pokemon['status'],
        'naturaleza' => $pokemon['naturaleza'] ?? 'Desconocida',
        'stat_aumentado' => $pokemon['stat_aumentado'],
        'stat_reducido' => $pokemon['stat_reducido'],
        'habilidad' => $pokemon['habilidad'] ?? 'Desconocida',
        'habilidad_descripcion' => $pokemon['habilidad_desc'],
        'tipo_primario' => $pokemon['tipo_primario'] ?? null,
        'tipo_primario_color' => $pokemon['tipo_primario_color'] ?? null,
        'tipo_secundario' => $pokemon['tipo_secundario'] ?? null,
        'tipo_secundario_color' => $pokemon['tipo_secundario_color'] ?? null,
    ],
    'stats' => $stats,
    'stats_base' => [
        'hp' => (int)$pokemon['base_hp'],
        'ataque' => (int)$pokemon['base_ataque'],
        'defensa' => (int)$pokemon['base_defensa'],
        'sp_ataque' => (int)$pokemon['base_sp_ataque'],
        'sp_defensa' => (int)$pokemon['base_sp_defensa'],
        'velocidad' => (int)$pokemon['base_velocidad'],
    ],
    'movimientos' => $movimientos,
    'movimientos_disponibles' => $movimientos_disponibles,
];

echo json_encode($response);
exit;
?>
