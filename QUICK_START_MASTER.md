# 🚀 Quick Start - Panel de Master

## ⚡ Inicio Rápido en 3 Pasos

### Paso 1: Configurar tu usuario como Master
```bash
# Abre PowerShell y ejecuta:
cd C:\xampp\htdocs\DAW_EJERCICIOS\Pokemonrol
mysql -u root -p rol
```

Luego en MySQL:
```sql
-- IMPORTANTE: Deshabilita las verificaciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Guarda tu ID actual
SET @mi_id = (SELECT id FROM usuarios WHERE correo = 'albertogarciaquintana5@gmail.com');

-- Actualiza todas las referencias en las tablas relacionadas
UPDATE inventario SET user_id = 67 WHERE user_id = @mi_id;
UPDATE pokemon_box SET user_id = 67 WHERE user_id = @mi_id;
UPDATE team SET user_id = 67 WHERE user_id = @mi_id;
UPDATE pokedex SET user_id = 67 WHERE user_id = @mi_id;

-- Ahora actualiza el usuario
UPDATE usuarios SET id = 67 WHERE correo = 'albertogarciaquintana5@gmail.com';

-- Vuelve a habilitar las verificaciones
SET FOREIGN_KEY_CHECKS = 1;

-- Verifica que funcionó:
SELECT id, nombre, correo FROM usuarios WHERE id = 67;

-- Si ves tu usuario, ¡perfecto! Sal de MySQL:
exit;
```

### Paso 2: Iniciar sesión
1. Abre: `http://localhost/DAW_EJERCICIOS/Pokemonrol/index.php`
2. Inicia sesión con tu cuenta
3. **¡Deberías ver un botón "Panel Master" arriba a la derecha!**

### Paso 3: Acceder al panel
1. Haz clic en "Panel Master"
2. ¡Ya puedes gestionar a tus jugadores!

---

## 🎯 Primeras Acciones

### Crear un jugador de prueba
```sql
-- En MySQL:
USE rol;

INSERT INTO usuarios (nombre, apellido, correo, contraseña, money) VALUES
('Test', 'Jugador', 'test@jugador.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 500.00);
-- Contraseña: "password"

-- Darle un Pokémon:
INSERT INTO pokemon_box (user_id, species_id, apodo, nivel, hp, max_hp, cp, status) 
VALUES (LAST_INSERT_ID(), 1, 'Pika Test', 5, 35, 35, 150, '');
```

### Probar el panel
1. En el panel de Master, haz clic en "Test Jugador"
2. Verás su Pikachu en la pestaña "Caja"
3. Haz clic en "Editar" junto al Pikachu
4. Cambia el nivel a 10 y HP a 50
5. Guarda cambios
6. ¡Los cambios se reflejan inmediatamente!

---

## 🎮 Funciones Principales

### Editar Pokémon
```
Panel → Seleccionar jugador → Tab "Equipo" o "Caja" → Botón "Editar"
```
Puedes cambiar:
- Nivel, HP, Estado, CP

### Dar Pokémon
```
Panel → Botón "Dar Pokémon" → Seleccionar jugador y especie → Confirmar
```

### Dar Items
```
Panel → Botón "Dar Item" → Seleccionar jugador e item → Confirmar
```

### Modificar Dinero
```
Panel → Seleccionar jugador → Botón "Editar dinero" junto al saldo
```

---

## 📖 Documentación Completa
 los comandos SQL completos del Paso 1 (incluyendo SET FOREIGN_KEY_CHECKS)
- Asegúrate de actualizar TODAS las tablas relacionadas antes del usuario
- **Guía detallada**: [MASTER_PANEL_GUIDE.md](MASTER_PANEL_GUIDE.md)
- **Resumen técnico**: [MASTER_PANEL_SUMMARY.md](MASTER_PANEL_SUMMARY.md)
- **Verificación**: `migrations/verificacion-master.sql`

---

## ❓ Problemas Comunes

### No veo el botón "Panel Master"
- Verifica que tu ID sea 67: `SELECT id FROM usuarios WHERE correo = 'tu@correo.com'`
- Cierra sesión y vuelve a iniciar sesión

### Error "No autorizado"
- Tu usuario no tiene ID 67
- Ejecuta: `UPDATE usuarios SET id = 67 WHERE correo = 'tu@correo.com'`

### No puedo editar Pokémon
- Verifica que Apache y MySQL estén corriendo en XAMPP
- Abre la consola del navegador (F12) para ver errores
- Comprueba que las APIs estén en `/api/admin_*.php`

### Los movimientos no aparecen
- Los movimientos deben estar en la tabla `pokemon_movimiento`
- Ejecuta la migración `009-add-pokemon-stats-system.sql` si no existe

---

## 🎲 Ejemplo de Sesión de Rol

```
1. Jugador inicia combate
   → Tú ves su equipo en el panel

2. Pokémon recibe daño
   → Editas el Pokémon → Reduces HP

3. Jugador usa poción
   → El jugador lo hace desde SU dashboard
   → HP se restaura automáticamente

4. Victoria: Pokémon sube de nivel
   → Editas el Pokémon → Subes nivel y HP max

5. Recompensa
   → Das items/dinero/Pokémon según corresponda
```

---

**¡Listo para empezar! 🎮**

Si tienes dudas, consulta [MASTER_PANEL_GUIDE.md](MASTER_PANEL_GUIDE.md)
