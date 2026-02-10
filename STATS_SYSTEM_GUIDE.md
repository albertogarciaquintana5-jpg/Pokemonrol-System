# 📊 SISTEMA DE ESTADÍSTICAS DE POKÉMON - GUÍA COMPLETA

## 🎯 RESUMEN DE LO QUE SE IMPLEMENTÓ

He creado un sistema completo de estadísticas para los Pokémon con:

✅ **Visualización de Stats en Rombo** - Los 6 stats principales en forma de rombo dinámico
✅ **Naturaleza** - Afecta stats (10% boost en uno, 10% reducción en otro)
✅ **Habilidad** - Describe la habilidad especial del Pokémon
✅ **4 Movimientos** - Cada Pokémon puede conocer hasta 4 movimientos
✅ **Recordar/Olvidar Movimientos** - Sistema para enseñar/olvidar movimientos
✅ **Movimientos Disponibles** - Lista de movimientos que la especie puede aprender

---

## 📁 ARCHIVOS NUEVOS Y MODIFICADOS

### Nuevos archivos creados:

1. **`migrations/009-add-pokemon-stats-system.sql`**
   - Crea todas las tablas necesarias
   - Inserta datos de ejemplo
   - Contiene tipos, naturalezas, habilidades, movimientos

2. **`api/get_pokemon_info.php`** 
   - GET: Devuelve información detallada del Pokémon
   - Calcula stats finales con modificadores de naturaleza
   - Incluye movimientos y disponibles

3. **`api/learn_move.php`**
   - POST: Enseña/olvida movimientos
   - Transaccional para seguridad

4. **`scripts/pokemon-info.js`**
   - Todo el JavaScript del modal
   - Renderiza el rombo de stats
   - Maneja aprender/olvidar movimientos

### Archivos modificados:

1. **`dashboard.php`**
   - Añadidos botones "ℹ️ Info" en caja y equipo
   - Incluye script pokemon-info.js
   - Modal nuevo para mostrar información

2. **`style.css`**
   - Estilos para el modal
   - Estilos para el rombo de stats
   - Estilos para las tarjetas de movimientos

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Tablas nuevas:

```
tipos
├── id (INT)
├── nombre (VARCHAR) - "Fuego", "Agua", etc.
└── color (VARCHAR) - Color hex (#FF5733)

naturalezas  
├── id (INT)
├── nombre (VARCHAR) - "Audaz", "Arisca", etc.
├── stat_aumentado (VARCHAR) - 'ataque', 'defensa', etc.
└── stat_reducido (VARCHAR) - ídem

habilidades
├── id (INT)
├── nombre (VARCHAR) - "Intimidación", "Sintonía", etc.
└── descripcion (TEXT)

movimientos
├── id (INT)
├── nombre (VARCHAR) - "Puño Fuego", "Rayo Hielo", etc.
├── tipo_id (INT) - FK a tipos
├── categoria (ENUM) - 'fisico', 'especial', 'estado'
├── potencia (INT) - Daño base (0 si no causa daño)
├── precision (INT) - 0-100 (%)
├── pp (INT) - Power Points (usos máximos)
└── descripcion (TEXT)

pokemon_species (ACTUALIZADA)
├── ... columnas existentes ...
├── hp (INT) - Stat base
├── ataque (INT)
├── defensa (INT)
├── sp_ataque (INT)
├── sp_defensa (INT)
└── velocidad (INT)

pokemon_box (ACTUALIZADA)
├── ... columnas existentes ...
├── naturaleza_id (INT) - FK a naturalezas
└── habilidad_id (INT) - FK a habilidades

pokemon_movimiento (NUEVA)
├── pokemon_box_id (INT) - FK a pokemon_box
├── movimiento_id (INT) - FK a movimientos
├── slot (TINYINT) - 1-4
├── pp_actual (INT) - PP restantes
└── PK (pokemon_box_id, movimiento_id, slot)

pokemon_species_movimiento (NUEVA)
├── species_id (INT) - FK a pokemon_species
├── movimiento_id (INT) - FK a movimientos
├── nivel (INT) - Nivel en que aprende
└── PK (species_id, movimiento_id)
```

---

## 🚀 CÓMO USAR (PASO A PASO)

### 1. EJECUTAR LA MIGRACIÓN SQL

```bash
mysql -u root -p rol < migrations/009-add-pokemon-stats-system.sql
```

Esto:
- Crea todas las tablas
- Inserta 18 tipos de Pokémon
- Inserta 25 naturalezas diferentes
- Inserta 10 habilidades de ejemplo
- Inserta 15 movimientos de ejemplo
- Asigna stats base a los 5 Pokémon de ejemplo
- Asigna naturalezas y habilidades a los Pokémon existentes

### 2. AÑADIR MÁS POKÉMON

Actualiza los stats base de especies existentes:

```sql
UPDATE pokemon_species SET 
  hp = 50,
  ataque = 52,
  defensa = 43,
  sp_ataque = 60,
  sp_defensa = 50,
  velocidad = 65
WHERE nombre = 'Charmander';
```

### 3. ASIGNAR NATURALEZA Y HABILIDAD A UN POKÉMON

```sql
UPDATE pokemon_box SET 
  naturaleza_id = (SELECT id FROM naturalezas WHERE nombre='Audaz'),
  habilidad_id = (SELECT id FROM habilidades WHERE nombre='Intimidación')
WHERE id = 1;  -- ID del Pokémon en la caja
```

### 4. ENSEÑAR MOVIMIENTO A UN POKÉMON

**Vía SQL (manual):**

```sql
INSERT INTO pokemon_movimiento (pokemon_box_id, movimiento_id, slot, pp_actual)
VALUES (1, (SELECT id FROM movimientos WHERE nombre='Puño Fuego'), 1, 15);
```

**Vía API (mediante UI):**
- Abre el panel
- Haz click en "ℹ️ Info" en un Pokémon
- En la sección "Movimientos disponibles para aprender"
- Haz click en "Enseñar"
- Se añadirá automáticamente al primer slot vacío

---

## 📊 FÓRMULA DE CÁLCULO DE STATS

```
Stat Final = floor( ((2 × Base + IV + (EV/4)) × Nivel / 100 + 5) × Modificador )
```

Donde:
- **Base**: Stat base de la especie (ej: Bulbasaur hp=45)
- **IV**: Individual Value (siempre 31 en nuestro sistema por ahora)
- **EV**: Effort Value (siempre 0 por ahora, pero extensible)
- **Nivel**: Nivel del Pokémon capturado (ej: 5)
- **Modificador**: 1.1 si naturaleza potencia, 0.9 si reduce, 1.0 si neutral

**Ejemplo:**
```
Bulbasaur, Nivel 5, Naturaleza Audaz (potencia Ataque):
Ataque = floor( ((2×49 + 31 + 0) × 5 / 100 + 5) × 1.1 )
       = floor( (129 × 0.05 + 5) × 1.1 )
       = floor( 11.45 × 1.1 )
       = floor( 12.595 )
       = 12
```

---

## 💾 DATOS DE EJEMPLO INCLUIDOS

### Tipos (18):
Normal, Fuego, Agua, Planta, Eléctrico, Hielo, Lucha, Veneno, Tierra, Volador, Psíquico, Bicho, Roca, Fantasma, Dragón, Siniestro, Acero, Hada

### Naturalezas (25):
Adamantina, Arisca, Audaz, Auspiciosa, Calmada, Cauta, Comedida, Desenfadada, Docil, Dura, Espigada, Estable, Firme, Floja, Grosera, Huraña, Ingenua, Leal, Miedosa, Mansa, Modesta, Parca, Plácida, Recia, Tímida

### Habilidades (10):
Estática, Sintonía, Torrente, Sobrecarga, Marcha acuática, Intimidación, Competencia, Absorción, Rivalidad, Premonición

### Movimientos (15):
Ataque Rápido, Bofetón Lodo, Placaje, Puño Fuego, Rayo Hielo, Rayo, Poder Psíquico, Terremoto, Destello Espectral, Danza Espada, Defensa Férrea, Síntesis, Recuperación, Protección, Rueda de Fuego

---

## 🎮 CÓMO FUNCIONA EN LA UI

### 1. Botón "ℹ️ Info"
Aparece en:
- Cada Pokémon en la **Caja Pokémon** (pestaña 📦)
- Cada Pokémon en el **Equipo** (pestaña ⚔️)

### 2. Modal de Información
Al hacer click abre un modal grande con:

#### Sección superior:
- Nombre/apodo del Pokémon
- Sprite grande
- Nivel, HP actual/máximo

#### Rombo de Stats:
- Visualización hexagonal en rombo
- 6 stats: HP, ATQ, DEF, ESP.ATQ, ESP.DEF, VEL
- Tamaño proporcional al valor
- El tamaño es relativo al máximo valor

#### Naturaleza + Habilidad:
- Nombre de la naturaleza
- Stat aumentado (en verde ↑)
- Stat disminuido (en rojo ↓)
- Nombre de la habilidad
- Descripción de la habilidad

#### Movimientos Actuales:
- Lista de hasta 4 movimientos
- Para cada movimiento:
  - Slot (1-4)
  - Tipo (con color)
  - Potencia y precisión
  - PP actual/máximo (barra de progreso)
  - Botón para olvidar

#### Movimientos Disponibles:
- Lista de movimientos que la especie puede aprender
- Nivel en que se aprende
- Botón "Enseñar" (si hay slots vacíos)

---

## 📝 EJEMPLOS SQL ADICIONALES

### Añadir nuevo tipo:
```sql
INSERT INTO tipos (nombre, color) VALUES ('Cristal', '#E0B4FF');
```

### Añadir nueva habilidad:
```sql
INSERT INTO habilidades (nombre, descripcion, efecto) VALUES 
('Guardia Espectral', 'No puede ser afectado por ataques tipo fantasma', 'immune_ghost');
```

### Añadir nuevo movimiento:
```sql
INSERT INTO movimientos (nombre, tipo_id, categoria, potencia, precision, pp, descripcion) VALUES 
('Garra Metálica', 
 (SELECT id FROM tipos WHERE nombre='Acero'),
 'fisico',
 50, 100, 35,
 'Ataque con garras de acero que reduce la defensa del enemigo');
```

### Enseñar movimiento a una especie (para que la aprenda al capturarla):
```sql
INSERT INTO pokemon_species_movimiento (species_id, movimiento_id, nivel) VALUES
(1, (SELECT id FROM movimientos WHERE nombre='Garra Metálica'), 10);
-- Bulbasaur aprenderá "Garra Metálica" al nivel 10
```

### Ver todos los datos de un Pokémon capturado:
```sql
SELECT 
  pb.id, pb.apodo, pb.nivel,
  ps.nombre, n.nombre AS naturaleza, h.nombre AS habilidad
FROM pokemon_box pb
JOIN pokemon_species ps ON pb.species_id = ps.id
LEFT JOIN naturalezas n ON pb.naturaleza_id = n.id
LEFT JOIN habilidades h ON pb.habilidad_id = h.id
WHERE pb.user_id = 1;
```

### Ver movimientos de un Pokémon:
```sql
SELECT pm.slot, m.nombre, t.nombre AS tipo, m.potencia, m.precision, m.pp, pm.pp_actual
FROM pokemon_movimiento pm
JOIN movimientos m ON pm.movimiento_id = m.id
LEFT JOIN tipos t ON m.tipo_id = t.id
WHERE pm.pokemon_box_id = 1;
```

---

## 🔄 FLUJO COMPLETO DE EJEMPLO

1. **Usuario captura un Pokémon (Bulbasaur)**
   - Se inserta en `pokemon_box` con nivel 5

2. **Sistema asigna naturaleza aleatoria**
   - `naturaleza_id = 3` (Audaz) → +Ataque, -ESP.ATQ

3. **Sistema asigna habilidad aleatoria**
   - `habilidad_id = 1` (Estática)

4. **Usuario abre modal "Info"**
   - Se calcula: ataque = 12 (con modificador de naturaleza)
   - Se obtienen movimientos iniciales

5. **Usuario enseña "Puño Fuego"**
   - Se inserta en `pokemon_movimiento`
   - API devuelve lista actualizada

6. **Usuario olvida movimiento**
   - Se elimina fila de `pokemon_movimiento`
   - Modal se actualiza

---

## 🚨 NOTAS IMPORTANTES

⚠️ **IV y EV**: Actualmente simplificados (IV=31 siempre, EV=0 siempre). Si quieres sistema más complejo:
- Añade columnas `iv_hp, iv_ataque, ...` a `pokemon_box`
- Añade columnas `ev_hp, ev_ataque, ...` a `pokemon_box`
- Modifica cálculo en `get_pokemon_info.php`

⚠️ **Tipos de movimiento**: Verificar que los tipos usados en movimientos existan en tabla `tipos`

⚠️ **Orden de slots**: Si un Pokémon olvida un movimiento del slot 2, el slot 3 NO se reasigna automáticamente. Puedes implementar eso si quieres.

⚠️ **PP**: Se actualiza cuando se usa un movimiento (no implementado aún en batalla)

---

## 🎓 PRÓXIMAS MEJORAS SUGERIDAS

1. **Sistema de Batalla**
   - MOVE.potencia × STAT.ataque / DEFENSA del enemigo
   - Aplicar tipo effectiveness
   - Reducir PP en cada uso

2. **IVs y EVs Individuales**
   - Generar IVs aleatorios al capturar
   - EVs se ganan con cada victoria

3. **Status Effects**
   - Actualizar `status` en `pokemon_box`
   - Movimientos que aplican status
   - Items que curan status

4. **Compatibilidad de Tipos**
   - Tabla `type_effectiveness`
   - Cálculo de daño con ventajas/desventajas

---

¿Necesitas ayuda con algo en particular?
