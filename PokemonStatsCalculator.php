<?php
/**
 * Sistema de Cálculo de Estadísticas de Pokémon
 * Basado en las fórmulas de los juegos oficiales (simplificado)
 */

class PokemonStatsCalculator {
    
    /**
     * Calcula el HP máximo de un Pokémon según su nivel y stats base
     * Fórmula simplificada: floor((2 * Base + IV + EV/4) * Nivel / 100) + Nivel + 10
     */
    public static function calculateMaxHP($baseHP, $level, $iv = 15, $ev = 0) {
        return floor(((2 * $baseHP + $iv + floor($ev / 4)) * $level) / 100) + $level + 10;
    }
    
    /**
     * Calcula una stat general (Ataque, Defensa, etc.)
     * Fórmula: floor(((2 * Base + IV + EV/4) * Nivel / 100) + 5)
     */
    public static function calculateStat($baseStat, $level, $iv = 15, $ev = 0) {
        return floor(((2 * $baseStat + $iv + floor($ev / 4)) * $level) / 100) + 5;
    }
    
    /**
     * Calcula todas las stats de un Pokémon
     */
    public static function calculateAllStats($species_id, $level, $mysqli) {
        // Obtener stats base de la especie
        $sql = "SELECT hp, ataque, defensa, sp_ataque, sp_defensa, velocidad 
                FROM pokemon_species WHERE id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $species_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return [
                'max_hp' => self::calculateMaxHP($row['hp'], $level),
                'ataque' => self::calculateStat($row['ataque'], $level),
                'defensa' => self::calculateStat($row['defensa'], $level),
                'sp_ataque' => self::calculateStat($row['sp_ataque'], $level),
                'sp_defensa' => self::calculateStat($row['sp_defensa'], $level),
                'velocidad' => self::calculateStat($row['velocidad'], $level)
            ];
        }
        $stmt->close();
        
        return null;
    }
    
    /**
     * Actualiza las stats de un Pokémon cuando sube de nivel
     */
    public static function updatePokemonStats($pokemon_box_id, $new_level, $mysqli) {
        // Obtener species_id del pokémon
        $sql = "SELECT species_id, hp FROM pokemon_box WHERE id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $pokemon_box_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $species_id = $row['species_id'];
            $current_hp = $row['hp'];
            $stmt->close();
            
            // Calcular nuevas stats
            $newStats = self::calculateAllStats($species_id, $new_level, $mysqli);
            
            if ($newStats) {
                // Mantener proporción de HP (si tenía 80% de HP, mantener 80%)
                $new_hp = $current_hp; // Por defecto mantener HP actual
                
                // Si el HP actual es mayor que el nuevo máximo, ajustarlo
                if ($current_hp > $newStats['max_hp']) {
                    $new_hp = $newStats['max_hp'];
                }
                
                // Actualizar en la base de datos
                $sql = "UPDATE pokemon_box SET 
                        nivel = ?,
                        max_hp = ?,
                        hp = ?,
                        ataque = ?,
                        defensa = ?,
                        sp_ataque = ?,
                        sp_defensa = ?,
                        velocidad = ?
                        WHERE id = ?";
                
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param('iiiiiiiii', 
                    $new_level,
                    $newStats['max_hp'],
                    $new_hp,
                    $newStats['ataque'],
                    $newStats['defensa'],
                    $newStats['sp_ataque'],
                    $newStats['sp_defensa'],
                    $newStats['velocidad'],
                    $pokemon_box_id
                );
                
                $success = $stmt->execute();
                $stmt->close();
                
                return $success ? $newStats : false;
            }
        }
        
        return false;
    }
    
    /**
     * Inicializa las stats de un Pokémon recién capturado/creado
     */
    public static function initializePokemonStats($pokemon_box_id, $mysqli) {
        // Obtener nivel y species del pokémon
        $sql = "SELECT species_id, nivel FROM pokemon_box WHERE id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $pokemon_box_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $species_id = $row['species_id'];
            $nivel = $row['nivel'];
            $stmt->close();
            
            return self::updatePokemonStats($pokemon_box_id, $nivel, $mysqli);
        }
        
        return false;
    }
}
