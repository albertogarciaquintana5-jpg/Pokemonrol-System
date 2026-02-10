<?php
/**
 * Helper Functions para Pokemonrol
 * Funciones reutilizables para evitar duplicación de código
 */

/**
 * Calcula la URL del sprite con extensión correcta
 * Prioriza .jpg sobre .png ya que es el formato más común en este proyecto
 * 
 * @param string|null $sprite ID del sprite (ej: "1", "530")
 * @param string $basePath Ruta base al directorio de imágenes (por defecto relativa a este archivo)
 * @return string|null URL del sprite con extensión, o null si no existe sprite ID
 */
function calculate_sprite_url($sprite, $basePath = null) {
    if (empty($sprite)) {
        return null;
    }
    
    // Si no se proporciona basePath, usar el predeterminado
    if ($basePath === null) {
        $basePath = __DIR__ . '/img/pokemon/';
    }
    
    // Asegurar que basePath termina con /
    if (substr($basePath, -1) !== '/') {
        $basePath .= '/';
    }
    
    $jpgPath = $basePath . $sprite . '.jpg';
    $pngPath = $basePath . $sprite . '.png';
    
    // Primero intentar JPG (más común en este proyecto)
    if (file_exists($jpgPath)) {
        return $sprite . '.jpg';
    } elseif (file_exists($pngPath)) {
        return $sprite . '.png';
    } else {
        // Fallback: usar .jpg por defecto (se mostrará error en navegador si no existe)
        return $sprite . '.jpg';
    }
}

/**
 * Añade sprite_url a un array de resultados de base de datos
 * Modifica el array directamente
 * 
 * @param array &$row Referencia al array (será modificado)
 * @param string $spriteField Nombre del campo que contiene el sprite ID (por defecto 'sprite')
 * @param string $basePath Ruta base opcional
 * @return void
 */
function add_sprite_url(&$row, $spriteField = 'sprite', $basePath = null) {
    if (isset($row[$spriteField])) {
        $row['sprite_url'] = calculate_sprite_url($row[$spriteField], $basePath);
    } else {
        $row['sprite_url'] = null;
    }
}

/**
 * Procesa múltiples filas añadiendo sprite_url a cada una
 * 
 * @param array &$rows Array de arrays (será modificado)
 * @param string $spriteField Nombre del campo que contiene el sprite ID
 * @param string $basePath Ruta base opcional
 * @return void
 */
function add_sprite_url_bulk(&$rows, $spriteField = 'sprite', $basePath = null) {
    foreach ($rows as &$row) {
        add_sprite_url($row, $spriteField, $basePath);
    }
}
