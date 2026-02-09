<?php
/**
 * Script de importación masiva de Pokémon desde PokéAPI
 * Genera 400 Pokémon con reglas específicas
 */

require_once 'db.php';

set_time_limit(600); // 10 minutos

// Configuración
$TARGET_COUNT = 400;
$API_BASE = 'https://pokeapi.co/api/v2';

// Mapeo de tipos inglés -> español (IDs de tu BD)
$TYPE_MAP = [
    'normal' => 1,
    'fire' => 2, 
    'water' => 3,
    'grass' => 4,
    'electric' => 5,
    'ice' => 6,
    'fighting' => 7,
    'poison' => 8,
    'ground' => 9,
    'flying' => 10,
    'psychic' => 11,
    'bug' => 12,
    'rock' => 13,
    'ghost' => 14,
    'dragon' => 15,
    'dark' => 16,
    'steel' => 17,
    'fairy' => 18
];

// IDs de iniciales por generación
$STARTERS = [
    // Gen 1
    [1, 2, 3],     // Bulbasaur line
    [4, 5, 6],     // Charmander line
    [7, 8, 9],     // Squirtle line
    // Gen 2
    [152, 153, 154], // Chikorita line
    [155, 156, 157], // Cyndaquil line
    [158, 159, 160], // Totodile line
    // Gen 3
    [252, 253, 254], // Treecko line
    [255, 256, 257], // Torchic line
    [258, 259, 260], // Mudkip line
    // Gen 4
    [387, 388, 389], // Turtwig line
    [390, 391, 392], // Chimchar line
    [393, 394, 395], // Piplup line
    // Gen 5
    [495, 496, 497], // Snivy line
    [498, 499, 500], // Tepig line
    [501, 502, 503], // Oshawott line
    // Gen 6
    [650, 651, 652], // Chespin line
    [653, 654, 655], // Fennekin line
    [656, 657, 658], // Froakie line
    // Gen 7
    [722, 723, 724], // Rowlet line
    [725, 726, 727], // Litten line
    [728, 729, 730], // Popplio line
    // Gen 8
    [810, 811, 812], // Grookey line
    [813, 814, 815], // Scorbunny line
    [816, 817, 818], // Sobble line
    // Gen 9
    [906, 907, 908], // Sprigatito line
    [909, 910, 911], // Fuecoco line
    [912, 913, 914], // Quaxly line
];

// Pseudo-legendarios (líneas completas)
$PSEUDO_LEGENDARIES = [
    [147, 148, 149], // Dratini line
    [246, 247, 248], // Larvitar line
    [371, 372, 373], // Bagon line
    [374, 375, 376], // Beldum line
    [443, 444, 445], // Gible line
    [633, 634, 635], // Deino line
    [704, 705, 706], // Goomy line
    [782, 783, 784], // Jangmo-o line
    [885, 886, 887], // Dreepy line
    [996, 997, 998], // Frigibax line
];

// Línea de Eevee
$EEVEE_LINE = [133, 134, 135, 136, 196, 197, 470, 471, 700]; // Eevee + 8 evoluciones

// Legendarios y míticos (EXCLUIR)
$EXCLUDED = [
    150, 151, // Mewtwo, Mew
    144, 145, 146, // Pájaros legendarios
    243, 244, 245, 249, 250, 251, // Perros + Lugia, Ho-Oh, Celebi
    377, 378, 379, 380, 381, 382, 383, 384, 385, 386, // Regis + Lati + Groudon/Kyogre/Rayquaza + Jirachi + Deoxys
    480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, // Gen 4 legendarios
    494, 638, 639, 640, 641, 642, 643, 644, 645, 646, 647, 648, 649, // Gen 5 legendarios
    716, 717, 718, 719, 720, 721, // Gen 6 legendarios
    785, 786, 787, 788, 789, 790, 791, 792, 800, 801, 802, 807, 808, 809, // Gen 7 legendarios + UBs
    888, 889, 890, 891, 892, 893, 894, 895, 896, 897, 898, // Gen 8 legendarios
    905, 1001, 1002, 1003, 1004, 1005, 1006, 1007, 1008, 1009, 1010, 1024, 1025, // Gen 9 legendarios
];

echo "🔄 Iniciando importación de Pokémon desde PokéAPI...\n\n";

$selected = [];
$stats_data = [];

// 1. Agregar todos los iniciales
echo "📝 Agregando iniciales obligatorios...\n";
foreach ($STARTERS as $line) {
    foreach ($line as $id) {
        $selected[] = $id;
    }
}
echo "✅ Iniciales: " . count($selected) . " Pokémon\n\n";

// 2. Agregar pseudo-legendarios
echo "📝 Agregando pseudo-legendarios...\n";
foreach ($PSEUDO_LEGENDARIES as $line) {
    foreach ($line as $id) {
        if (!in_array($id, $selected)) {
            $selected[] = $id;
        }
    }
}
echo "✅ Total con pseudo-legendarios: " . count($selected) . " Pokémon\n\n";

// 3. Agregar línea de Eevee
echo "📝 Agregando línea de Eevee...\n";
foreach ($EEVEE_LINE as $id) {
    if (!in_array($id, $selected)) {
        $selected[] = $id;
    }
}
echo "✅ Total con Eevee: " . count($selected) . " Pokémon\n\n";

// 4. Función para obtener línea evolutiva completa
function getEvolutionChain($pokemon_id) {
    global $API_BASE;
    
    try {
        // Obtener species para encontrar evolution chain
        $species_data = @file_get_contents("$API_BASE/pokemon-species/$pokemon_id");
        if (!$species_data) return [$pokemon_id];
        
        $species = json_decode($species_data, true);
        $chain_url = $species['evolution_chain']['url'];
        
        $chain_data = @file_get_contents($chain_url);
        if (!$chain_data) return [$pokemon_id];
        
        $chain = json_decode($chain_data, true);
        
        // Extraer todos los IDs de la cadena
        $line = [];
        $current = $chain['chain'];
        
        while ($current) {
            $id = (int) str_replace($API_BASE . '/pokemon-species/', '', rtrim($current['species']['url'], '/'));
            $line[] = $id;
            
            $current = !empty($current['evolves_to']) ? $current['evolves_to'][0] : null;
        }
        
        return $line;
    } catch (Exception $e) {
        return [$pokemon_id];
    }
}

// 5. Rellenar hasta 400 con Pokémon aleatorios
echo "📝 Rellenando con Pokémon aleatorios (diversidad de tipos)...\n";
$attempts = 0;
$max_attempts = 1000;

while (count($selected) < $TARGET_COUNT && $attempts < $max_attempts) {
    $attempts++;
    
    // Rango: Gen 1-8 (hasta ~900, evitamos Gen 9 parcial)
    $random_id = rand(1, 905);
    
    // Verificar exclusiones
    if (in_array($random_id, $EXCLUDED)) continue;
    if (in_array($random_id, $selected)) continue;
    
    // Obtener línea evolutiva completa
    $line = getEvolutionChain($random_id);
    
    // Verificar que ninguno esté excluido
    $skip = false;
    foreach ($line as $id) {
        if (in_array($id, $EXCLUDED)) {
            $skip = true;
            break;
        }
    }
    if ($skip) continue;
    
    // Verificar que no nos pasemos de 400
    $new_total = count($selected) + count($line);
    if ($new_total > $TARGET_COUNT) {
        // Si la línea es muy grande, saltarla
        if (count($line) > 3) continue;
        // Si solo nos faltan pocos, saltarla si no cabe
        if ($TARGET_COUNT - count($selected) < count($line)) continue;
    }
    
    // Agregar toda la línea
    foreach ($line as $id) {
        if (!in_array($id, $selected)) {
            $selected[] = $id;
        }
    }
    
    if ($attempts % 50 == 0) {
        echo "   Progreso: " . count($selected) . "/$TARGET_COUNT\n";
    }
}

echo "✅ Total seleccionado: " . count($selected) . " Pokémon\n\n";

// 6. Obtener datos de PokéAPI
echo "🌐 Descargando datos desde PokéAPI...\n";
$progress = 0;
foreach ($selected as $id) {
    $progress++;
    
    // Obtener datos del Pokémon
    $pokemon_data = @file_get_contents("$API_BASE/pokemon/$id");
    if (!$pokemon_data) {
        echo "⚠️  Error al obtener Pokémon #$id, saltando...\n";
        continue;
    }
    
    $pokemon = json_decode($pokemon_data, true);
    
    // Extraer stats
    $stats = [
        'hp' => 0,
        'ataque' => 0,
        'defensa' => 0,
        'velocidad' => 0,
        'sp_ataque' => 0,
        'sp_defensa' => 0
    ];
    
    foreach ($pokemon['stats'] as $stat) {
        $stat_name = $stat['stat']['name'];
        $base_stat = $stat['base_stat'];
        
        switch ($stat_name) {
            case 'hp': $stats['hp'] = $base_stat; break;
            case 'attack': $stats['ataque'] = $base_stat; break;
            case 'defense': $stats['defensa'] = $base_stat; break;
            case 'speed': $stats['velocidad'] = $base_stat; break;
            case 'special-attack': $stats['sp_ataque'] = $base_stat; break;
            case 'special-defense': $stats['sp_defensa'] = $base_stat; break;
        }
    }
    
    // Extraer tipos
    $tipo_primario = null;
    $tipo_secundario = null;
    
    if (isset($pokemon['types'][0])) {
        $type_name = $pokemon['types'][0]['type']['name'];
        $tipo_primario = $TYPE_MAP[$type_name] ?? null;
    }
    
    if (isset($pokemon['types'][1])) {
        $type_name = $pokemon['types'][1]['type']['name'];
        $tipo_secundario = $TYPE_MAP[$type_name] ?? null;
    }
    
    // Nombre y sprite (usar ID en lugar de nombre)
    $nombre = ucfirst($pokemon['name']);
    $sprite = $id; // Guardar solo el ID
    
    $stats_data[$id] = [
        'id' => $id,
        'nombre' => $nombre,
        'sprite' => $sprite,
        'tipo_primario' => $tipo_primario,
        'tipo_secundario' => $tipo_secundario,
        'hp' => $stats['hp'],
        'ataque' => $stats['ataque'],
        'defensa' => $stats['defensa'],
        'velocidad' => $stats['velocidad'],
        'sp_ataque' => $stats['sp_ataque'],
        'sp_defensa' => $stats['sp_defensa']
    ];
    
    if ($progress % 20 == 0) {
        echo "   Descargados: $progress/" . count($selected) . "\n";
    }
    
    // Pequeño delay para no saturar la API
    usleep(100000); // 0.1 segundos
}

echo "✅ Datos descargados: " . count($stats_data) . " Pokémon\n\n";

// 7. Generar archivo SQL
echo "📄 Generando archivo SQL...\n";

$sql_content = "-- =====================================================\n";
$sql_content .= "-- IMPORTACIÓN MASIVA DE 400 POKÉMON DESDE PokéAPI\n";
$sql_content .= "-- Generado automáticamente el " . date('Y-m-d H:i:s') . "\n";
$sql_content .= "-- =====================================================\n\n";
$sql_content .= "USE rol;\n\n";
$sql_content .= "-- Deshabilitar checks temporalmente para limpiar\n";
$sql_content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
$sql_content .= "-- Limpiar tabla actual\n";
$sql_content .= "TRUNCATE TABLE pokemon_species;\n\n";
$sql_content .= "-- Rehabilitar checks\n";
$sql_content .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
$sql_content .= "-- Orden de stats: hp, ataque, defensa, velocidad, sp_ataque, sp_defensa\n\n";
$sql_content .= "INSERT INTO `pokemon_species` \n";
$sql_content .= "  (`id`, `nombre`, `sprite`, `tipo_primario_id`, `tipo_secundario_id`, `hp`, `ataque`, `defensa`, `velocidad`, `sp_ataque`, `sp_defensa`) \nVALUES\n";

$values = [];
foreach ($stats_data as $pokemon) {
    $tipo_sec = $pokemon['tipo_secundario'] ? $pokemon['tipo_secundario'] : 'NULL';
    
    $values[] = sprintf(
        "  (%d, '%s', %d, %d, %s, %d, %d, %d, %d, %d, %d)",
        $pokemon['id'],
        $mysqli->real_escape_string($pokemon['nombre']),
        $pokemon['sprite'],
        $pokemon['tipo_primario'],
        $tipo_sec,
        $pokemon['hp'],
        $pokemon['ataque'],
        $pokemon['defensa'],
        $pokemon['velocidad'],
        $pokemon['sp_ataque'],
        $pokemon['sp_defensa']
    );
}

$sql_content .= implode(",\n", $values) . ";\n";

// Guardar archivo
$filename = 'migrations/import_400_pokemon_' . date('Ymd_His') . '.sql';
file_put_contents($filename, $sql_content);

echo "✅ Archivo SQL generado: $filename\n\n";

// 8. Ejecutar la importación
echo "💾 ¿Deseas ejecutar la importación ahora? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim(strtolower($line)) == 's') {
    echo "🔄 Ejecutando importación...\n";
    echo "⚠️  ADVERTENCIA: Esto eliminará todos los Pokémon actuales y sus referencias.\n";
    echo "   ¿Estás seguro? (s/n): ";
    $confirm = fgets($handle);
    
    if (trim(strtolower($confirm)) != 's') {
        echo "⏭️  Importación cancelada.\n";
        exit;
    }
    
    // Limpiar tablas relacionadas primero
    echo "🧹 Limpiando tablas relacionadas...\n";
    $mysqli->query("DELETE FROM pokedex");
    $mysqli->query("DELETE FROM pokemon_box");
    echo "✅ Tablas relacionadas limpiadas\n";
    
    echo "📥 Insertando Pokémon...\n";
    $mysqli->multi_query($sql_content);
    
    // Esperar a que terminen todas las consultas
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    if ($mysqli->errno) {
        echo "❌ Error: " . $mysqli->error . "\n";
    } else {
        // Verificar cuántos se insertaron
        $count_result = $mysqli->query("SELECT COUNT(*) as total FROM pokemon_species");
        $count = $count_result->fetch_assoc()['total'];
        
        echo "✅ Importación completada: $count Pokémon insertados\n";
    }
} else {
    echo "⏭️  Importación cancelada. Puedes ejecutar el archivo SQL manualmente.\n";
}
fclose($handle);

echo "\n🎉 Proceso completado!\n";
echo "\n📊 Resumen:\n";
echo "   - Iniciales: ~81\n";
echo "   - Pseudo-legendarios: ~30\n";
echo "   - Línea Eevee: 9\n";
echo "   - Otros: ~280\n";
echo "   - Total: " . count($stats_data) . " Pokémon\n";
?>
