# 🔧 TROUBLESHOOTING - SISTEMA DE ESTADÍSTICAS

## ❌ PROBLEMAS COMUNES Y SOLUCIONES

---

## 1️⃣ "El botón ℹ️ Info no aparece"

### Causa probable:
- El archivo `dashboard.php` no se actualizó correctamente
- El servidor no recargó los cambios

### Soluciones:
1. Verifica que `dashboard.php` contenga la línea:
   ```php
   <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(...)">ℹ️ Info</button>
   ```

2. Limpia caché del navegador:
   - Presiona `Ctrl+Shift+Delete`
   - Selecciona "Todos los tiempos"
   - Haz click en "Borrar"

3. Recarga la página: `F5` o `Ctrl+F5`

4. Si aún no aparece, busca en `dashboard.php` por "pokemon-info-btn" para verificar

---

## 2️⃣ "El modal se abre pero está vacío"

### Causa probable:
- El archivo `scripts/pokemon-info.js` no se cargó
- Error en la API `get_pokemon_info.php`
- Error de JavaScript

### Soluciones:
1. **Abre la consola del navegador** (F12 → Console)
   - Busca errores rojo
   - Lee el mensaje de error

2. **Verifica que el script se cargó:**
   - Abre F12 → Sources
   - Busca `pokemon-info.js`
   - Si no aparece, revisa que `dashboard.php` tenga:
     ```html
     <script src="scripts/pokemon-info.js"></script>
     ```

3. **Verifica la API:**
   - Abre F12 → Network
   - Haz click en "ℹ️ Info"
   - Busca `get_pokemon_info.php`
   - Si tiene error 404: El archivo no existe. Verifica que esté en `api/`
   - Si tiene error 500: Hay error en PHP. Revisa logs

4. **Revisa los logs de PHP:**
   ```
   C:\xampp\apache\logs\error.log
   ```

---

## 3️⃣ "El rombo de stats no se ve / se ve mal"

### Causa probable:
- CSS no se cargó
- SVG no se renderiza correctamente
- Navegador incompatible

### Soluciones:
1. **Verifica que los estilos se cargaron:**
   - F12 → Inspector
   - Busca clase `.stats-diamond`
   - Comprueba que existen los estilos

2. **Si SVG no funciona:**
   - Algunos navegadores viejos no soportan SVG
   - Intenta en Chrome o Firefox
   - Actualiza tu navegador

3. **Si las etiquetas no están centradas:**
   - Revisa `style.css` línea ~250-350
   - Busca secciones `.stat-label`
   - Verifica que existe `position: absolute`

4. **Solución rápida:**
   - Refresca F12 → Console
   - Ejecuta: `location.reload()`

---

## 4️⃣ "No puedo enseñar movimientos"

### Causa probable:
- La migración SQL no se ejecutó
- Las tablas no existen
- Hay error en `learn_move.php`

### Soluciones:
1. **Verifica que la migración se ejecutó:**
   ```sql
   SHOW TABLES; -- Debe incluir: movimientos, pokemon_movimiento, etc.
   ```

2. **Si no existen las tablas:**
   - Ejecuta: `mysql -u root -p rol < migrations/009-add-pokemon-stats-system.sql`
   - Verifica que no hay errores

3. **Si el botón "Enseñar" no hace nada:**
   - F12 → Console
   - Busca errores
   - F12 → Network
   - Mira si `learn_move.php` responde
   - Si error 500, revisa logs PHP

4. **Si dice "ya tiene 4 movimientos":**
   - Ese es el comportamiento correcto
   - Primero olvida uno: botón "Olvidar"
   - Luego enseña el nuevo

---

## 5️⃣ "Error: 'undefined is not a function' en consola"

### Causa probable:
- `pokemon-info.js` no se cargó antes de `dashboard.php`
- Función no existe

### Soluciones:
1. **Revisa que el script está en el orden correcto:**
   ```html
   <script src="scripts/pokemon-info.js"></script>  <!-- ANTES -->
   <script>
     // Código del dashboard
   </script>
   ```

2. **Si vuelve a fallar:**
   - Busca en consola qué función falta
   - Verifica que esté en `pokemon-info.js`
   - Copia el nombre exacto (mayúsculas/minúsculas)

---

## 6️⃣ "La base de datos no tiene los datos de ejemplo"

### Causa probable:
- La migración no se ejecutó correctamente
- Hay error SQL

### Soluciones:
1. **Verifica las tablas:**
   ```sql
   SELECT COUNT(*) FROM tipos;         -- Debe ser ≥ 18
   SELECT COUNT(*) FROM naturalezas;   -- Debe ser ≥ 25
   SELECT COUNT(*) FROM habilidades;   -- Debe ser ≥ 10
   SELECT COUNT(*) FROM movimientos;   -- Debe ser ≥ 15
   ```

2. **Si están vacías:**
   - Abre el archivo: `migrations/009-add-pokemon-stats-system.sql`
   - Busca sección "SEED DATA"
   - Copia el contenido INSERT
   - Ejecuta manualmente en phpMyAdmin o terminal

3. **Si dice "DUPLICATE KEY ERROR":**
   - Los datos ya existen
   - Eso es normal
   - Verifica que existen con SELECT

---

## 7️⃣ "Los movimientos no tienen tipo"

### Causa probable:
- Los tipos no se cargaron
- La FK `tipo_id` es NULL

### Soluciones:
1. **Verifica los tipos:**
   ```sql
   SELECT * FROM tipos; -- Debe tener resultados
   ```

2. **Verifica los movimientos:**
   ```sql
   SELECT m.nombre, t.nombre AS tipo
   FROM movimientos m
   LEFT JOIN tipos t ON m.tipo_id = t.id;
   ```

3. **Si `tipo_id` es NULL:**
   - Actualiza manualmente:
   ```sql
   UPDATE movimientos SET tipo_id = (SELECT id FROM tipos WHERE nombre='Fuego')
   WHERE nombre='Puño Fuego';
   ```

---

## 8️⃣ "El Pokémon no tiene naturaleza/habilidad"

### Causa probable:
- Los campos `naturaleza_id` y `habilidad_id` son NULL
- No se asignaron al crear el Pokémon

### Soluciones:
1. **Asigna manualmente:**
   ```sql
   UPDATE pokemon_box SET 
     naturaleza_id = (SELECT id FROM naturalezas ORDER BY RAND() LIMIT 1),
     habilidad_id = (SELECT id FROM habilidades ORDER BY RAND() LIMIT 1)
   WHERE user_id = 1 AND naturaleza_id IS NULL;
   ```

2. **O asigna específicamente:**
   ```sql
   UPDATE pokemon_box SET 
     naturaleza_id = 3,  -- Audaz
     habilidad_id = 1    -- Intimidación
   WHERE id = 1;
   ```

---

## 9️⃣ "Error 500 en get_pokemon_info.php"

### Causa probable:
- Error SQL
- Falta una tabla o columna
- Error en PHP

### Soluciones:
1. **Revisa los logs:**
   ```
   C:\xampp\apache\logs\error.log
   ```

2. **Verifica que las columnas existen en `pokemon_species`:**
   ```sql
   DESCRIBE pokemon_species; 
   -- Debe incluir: hp, ataque, defensa, velocidad, sp_ataque, sp_defensa
   ```

3. **Si faltan columnas:**
   ```sql
   ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS hp INT DEFAULT 45;
   ALTER TABLE pokemon_species ADD COLUMN IF NOT EXISTS ataque INT DEFAULT 49;
   -- ... etc para todas las 6
   ```

4. **Verifica que `pokemon_box` tiene las nuevas columnas:**
   ```sql
   DESCRIBE pokemon_box;
   -- Debe incluir: naturaleza_id, habilidad_id
   ```

5. **Si faltan:**
   ```sql
   ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS naturaleza_id INT;
   ALTER TABLE pokemon_box ADD COLUMN IF NOT EXISTS habilidad_id INT;
   ```

---

## 🔟 "No veo el historial D100 en BD"

### Nota:
- Esto está separado del sistema de stats
- Pero si recibes error en D100:

### Soluciones:
1. **Verifica que la tabla existe:**
   ```sql
   SHOW TABLES LIKE 'd100%';
   ```

2. **Si no existe:**
   ```sql
   CREATE TABLE d100_rolls (
     id INT AUTO_INCREMENT PRIMARY KEY,
     user_id INT NOT NULL,
     value INT NOT NULL,
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES usuarios(id)
   );
   ```

---

## 🆘 "NADA FUNCIONA, NECESITO EMPEZAR DE NUEVO"

### Reset completo:

1. **Elimina BD vieja:**
   ```sql
   DROP DATABASE rol;
   ```

2. **Ejecuta migración 004 (crea BD nueva):**
   ```bash
   mysql -u root -p < migrations/004-create-fresh-db.sql
   ```

3. **Ejecuta migración 009 (añade stats):**
   ```bash
   mysql -u root -p < migrations/009-add-pokemon-stats-system.sql
   ```

4. **Limpia caché navegador:**
   - Ctrl+Shift+Delete
   - Borra todo
   - Recarga página

5. **Prueba:**
   - Inicia sesión
   - Ve a Caja Pokémon
   - Haz click en "ℹ️ Info"

---

## 📞 VERIFICACIÓN FINAL

Ejecuta esto en la BD:

```sql
-- 1. Verifica tablas
SELECT COUNT(*) as tablas_stats FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'rol' AND TABLE_NAME IN (
  'tipos', 'naturalezas', 'habilidades', 'movimientos', 
  'pokemon_movimiento', 'pokemon_species_movimiento'
);
-- Debe retornar: 6

-- 2. Verifica datos
SELECT 
  (SELECT COUNT(*) FROM tipos) as tipos,
  (SELECT COUNT(*) FROM naturalezas) as naturalezas,
  (SELECT COUNT(*) FROM habilidades) as habilidades,
  (SELECT COUNT(*) FROM movimientos) as movimientos;
-- Debe retornar: 18+, 25+, 10+, 15+

-- 3. Verifica columnas en pokemon_box
DESCRIBE pokemon_box;
-- Debe incluir: naturaleza_id, habilidad_id

-- 4. Verifica Pokémon con stats
SELECT COUNT(*) FROM pokemon_species WHERE hp IS NOT NULL;
-- Debe retornar: 5+
```

Si todo retorna valores positivos, ¡está bien! 🎉

---

## 📚 DOCUMENTOS DE AYUDA

- **STATS_SYSTEM_GUIDE.md** - Guía completa técnica
- **SETUP_STATS_SYSTEM.md** - Primeros pasos
- **VISUAL_REFERENCE.md** - Referencia visual
- **Código en scripts/pokemon-info.js** - Comentado y legible

---

**¿Problema no resuelto?** 
Revisa:
1. F12 → Console (errores JavaScript)
2. F12 → Network (errores HTTP)
3. C:\xampp\apache\logs\error.log (errores PHP)
4. phpMyAdmin → Estructura de tablas (errores BD)

En el 99% de casos, uno de estos 4 te dirá exactamente qué está mal.
