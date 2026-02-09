# 📊 SISTEMA DE CÁLCULO AUTOMÁTICO DE ESTADÍSTICAS POKÉMON

## ✅ ¿Qué se ha implementado?

Se ha añadido un sistema completo que **calcula automáticamente** las estadísticas de los Pokémon al crearlos, usando las **fórmulas oficiales de los juegos Pokémon**.

---

## 🎯 Características

### 1. **Cálculo Automático al Insertar**
Cuando insertas un nuevo Pokémon en `pokemon_box`, el sistema:
- ✅ Genera IVs aleatorios (0-31) para cada estadística
- ✅ Calcula las stats basándose en estadísticas base de la especie
- ✅ Aplica modificadores de naturaleza si existe
- ✅ Usa las fórmulas oficiales de Pokémon
  - Como juegos originales: Gold, Crystal, Ruby, Emerald, etc.

### 2. **IVs (Individual Values)**
Cada Pokémon tiene IVs únicos que añaden variación natural (0-31 por stat):
- `iv_hp`
- `iv_ataque`
- `iv_defensa`
- `iv_sp_ataque`
- `iv_sp_defensa`
- `iv_velocidad`

### 3. **Fórmulas Oficiales**

**Para HP:**
```
HP = floor(((2 * Base + IV) * Level) / 100) + Level + 10
```

**Para otras stats:**
```
Stat = floor((floor(((2 * Base + IV) * Level) / 100) + 5) * Nature)
```

Donde:
- **Base**: Estadística base de `pokemon_species`
- **IV**: Valor individual (0-31)
- **Level**: Nivel del Pokémon
- **Nature**: Multiplicador de naturaleza (0.9, 1.0, o 1.1)

---

## 🚀 Cómo Usar

### **Opción 1: Insertar Pokémon simple (nivel automático = 5)**
```sql
INSERT INTO pokemon_box (user_id, species_id) 
VALUES (68, 1); -- Pikachu nivel 5
```

### **Opción 2: Especificar nivel**
```sql
INSERT INTO pokemon_box (user_id, species_id, nivel) 
VALUES (68, 1, 10); -- Pikachu nivel 10
```

### **Opción 3: Con naturaleza**
```sql
INSERT INTO pokemon_box (user_id, species_id, nivel, naturaleza_id) 
VALUES (68, 3, 5, 3); -- Bulbasaur nivel 5, naturaleza Audaz (+Atk, -SpAtk)
```

### **Opción 4: Con IVs personalizados** (para eventos especiales)
```sql
INSERT INTO pokemon_box (user_id, species_id, nivel, iv_hp, iv_ataque) 
VALUES (68, 1, 10, 31, 31); -- Pikachu con IVs máximos en HP y Ataque
```

---

## 📈 Subir de Nivel y Recalcular Stats

Cuando un Pokémon sube de nivel, usa el procedimiento almacenado:

```php
// PHP: Subir a nivel 20 y recalcular
$poke_id = 49;
$mysqli->query("UPDATE pokemon_box SET nivel = 20 WHERE id = $poke_id");
$mysqli->query("CALL recalculate_pokemon_stats($poke_id)");
```

```sql
-- SQL directo
UPDATE pokemon_box SET nivel = 20 WHERE id = 49;
CALL recalculate_pokemon_stats(49);
```

El procedimiento:
- ✅ Recalcula todas las estadísticas con el nuevo nivel
- ✅ Mantiene los IVs originales
- ✅ Ajusta HP actual proporcionalmente (no pierden vida al subir nivel)

---

## 📊 Ejemplo Práctico

### **Crear un Pikachu nivel 10**
```sql
INSERT INTO pokemon_box (user_id, species_id, nivel) VALUES (68, 1, 10);
```

**Resultado automático:**
- Stats base de Pikachu: HP=45, Atk=49, Def=49, SpAtk=65, SpDef=65, Spd=45
- IVs generados aleatoriamente: HP=3, Atk=2, Def=3, SpAtk=7, SpDef=28, Spd=25
- Stats calculadas: **HP=29, Atk=15, Def=15, SpAtk=18, SpDef=20, Spd=16**

---

## 🔧 Verificar que Funciona

Ejecuta el script de test:
```bash
php test_stats_calculation.php
```

Esto creará Pokémon de prueba, verificará las fórmulas y limpiará automáticamente.

---

## 💡 Notas Importantes

1. **NO necesitas calcular stats manualmente** - el trigger lo hace automát icamente
2. **Cada Pokémon es único** - IVs aleatorios hacen que dos Pikachu nivel 10 tengan stats ligeramente diferentes
3. **Naturalezas importan** - pueden hacer +10% en una stat y -10% en otra
4. **Compatible con sistema anterior** - Pokémon viejos sin IVs se les generan automáticamente al recalcular

---

## 📝 Archivos Creados

- `migrations/add_stats_calculation.sql` - Migración completa del sistema
- `test_stats_calculation.php` - Script de prueba y validación

---

## 🎮 Comportamiento Tipo Pokémon Original

Este sistema replica fielmente los juegos clásicos:
- ✅ Pikachu salvaje nivel 5 tendrá entre 18-22 HP (variación por IVs)
- ✅ Al subir de nivel, las stats crecen progresivamente
- ✅ Naturalezas afectan el crecimiento de stats
- ✅ IVs ocultos determinan el potencial máximo

**¡Ahora tu sistema de captura y equipo es igual que los juegos originales!** 🎉
