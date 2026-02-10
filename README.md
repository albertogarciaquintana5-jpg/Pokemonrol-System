# Pokémon Rol - Proyecto

Pequeña aplicación de ejemplo con login, registro y un panel de entrenador (Inventario, Caja, Equipo, Pokédex). Interfaz basada en Bootstrap y endpoints PHP que usan sesiones para identificar al usuario.

---

## Resumen rápido
- `index.php` / `login_process.php`: login.
- `register.php` / `register_process.php`: registro de usuarios.
- `dashboard.php`: panel (requiere sesión activa).
- APIs JSON en `api/` para operaciones de inventario y gestión de Pokémon.

## Base de datos y migraciones

Se incluyen varios scripts en `migrations/`:
- `002-team-trigger-and-samples.sql` — trigger/ejemplos (si existe).
- `003-add-item-effects-and-maxhp.sql` — añade `items.effect_type`, `items.effect_value` y `pokemon_box.max_hp`, `pokemon_box.status`.
- `004-create-fresh-db.sql` — crea una base `rol` desde cero y siembra datos de ejemplo (usuario, items, especies, pokemon, inventario, team, pokedex).

Importar la base nueva (PowerShell):
```powershell
mysql -u root -p < "C:\xampp\htdocs\DAW_EJERCICIOS\Pokemonrol\migrations\004-create-fresh-db.sql"
```

Notas:
- Si quieres reemplazar una base existente, descomenta (o ejecuta manualmente) `DROP DATABASE IF EXISTS rol;` dentro del `004-create-fresh-db.sql` antes de importarlo.
- La contraseña del usuario de ejemplo está en el script como hash bcrypt. Para generar tu propio hash con PHP usa:
```php
<?php echo password_hash('Albertosaurus2006', PASSWORD_DEFAULT); ?>
```
Reemplaza el campo `contraseña` en el INSERT si quieres usar ese hash.

## Endpoints principales (requieren sesión PHP)

### APIs para Jugadores
- `api/get_inventory.php` (GET) — devuelve inventario. Si la columna `items.effect_type` existe, el endpoint incluirá `effect_type` y `effect_value`.
- `api/use_item.php` (POST) — cuerpo JSON `{ item_id, [box_id] }`. Decrementa inventario y, si `box_id` y `items.effect_type` aplican, ejecuta el efecto (soporta `heal_flat`, `heal_percent`, `revive`, `clear_status`). Devuelve `inventory`, `remaining` y `applied` si hubo efecto.
- `api/get_box.php` (GET) — devuelve `pokemon_box` del usuario; incluye `max_hp`/`status` si existen.
- `api/get_team.php` (GET) — devuelve el equipo (slots); incluye `hp`, `max_hp`, `status` si existen.
- `api/move_pokemon.php` (POST) — `{ action: 'equip'|'unequip', box_id?, slot? }`.
- `api/send_item.php` (POST) — `{ item_id, to_email }` mueve 1 unidad (transaccional).
- `api/mark_pokedex.php` (POST) — `{ species_id, visto?, capturado? }`.

### APIs para el Master (Solo usuario ID 67)
- `api/admin_get_player.php` (GET) — `?user_id=X` devuelve todos los datos de un jugador (equipo, caja, inventario).
- `api/admin_get_pokemon.php` (GET) — `?pokemon_id=X` devuelve datos completos de un Pokémon con sus movimientos.
- `api/admin_update_pokemon.php` (POST) — JSON con `pokemon_id` y campos a actualizar (nivel, HP, status).
- `api/admin_give_pokemon.php` (POST) — `{ user_id, species_id, apodo?, nivel?, hp? }` crea un Pokémon en la caja del jugador.
- `api/admin_give_item.php` (POST) — `{ user_id, item_id, cantidad }` añade items al inventario.
- `api/admin_update_money.php` (POST) — `{ user_id, money }` actualiza el dinero del jugador.
- `api/admin_remove_item.php` (POST) — `{ user_id, item_id }` elimina un item del inventario.

**Nota**: Las APIs de admin verifican que el usuario actual tenga ID 67. Si no, devuelven error 'No autorizado'.

## Cómo probar desde el navegador

1. Inicia Apache/MySQL con XAMPP.
2. Importa la migración `004` para obtener datos de ejemplo.
3. Abre `http://localhost/DAW_EJERCICIOS/Pokemonrol/index.php`, inicia sesión con el usuario de ejemplo (correo: `albertogarciaquintana5@gmail.com`).

### Panel de Master (Game Master)
El sistema incluye un panel de administrador completo para el Master del juego de rol:
- **Acceso**: Solo disponible para el usuario con ID 67
- **Ubicación**: Botón "Panel Master" visible en el dashboard cuando eres Master
- **Funcionalidades**:
  - Ver todos los jugadores y sus datos
  - Gestionar equipos y cajas de Pokémon de cualquier jugador
  - Modificar stats (HP, nivel, estado)
  - Dar Pokémon y items a jugadores
  - Modificar dinero de jugadores
  
**Documentación completa**: Ver [MASTER_PANEL_GUIDE.md](MASTER_PANEL_GUIDE.md)

**Configurar usuario Master**: Usa `migrations/011-setup-master-user.sql` para configurar tu usuario con ID 67.

## Imágenes (Pokémon y Objetos)

El sistema está configurado para mostrar automáticamente imágenes de:
- **Pokémon**: desde el campo `pokemon_species.sprite`
- **Objetos**: desde el campo `items.icono`

### Estructura de carpetas
```
img/
├── pokemon/     (sprites de pokémon)
└── items/       (iconos de objetos)
```

### Cómo añadir imágenes

1. Descarga o crea tus imágenes PNG (idealmente con fondo transparente).
2. Coloca los archivos en las carpetas correspondientes (`img/pokemon/` o `img/items/`).
3. Actualiza la BD con los nombres de archivo:
   ```sql
   UPDATE pokemon_species SET sprite = 'bulbasaur.png' WHERE nombre = 'Bulbasaur';
   UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
   ```
4. Las imágenes aparecerán automáticamente en:
   - 🎒 Inventario (iconos de items)
   - 📦 Caja Pokémon (sprites de pokémon)
   - ⚔️ Equipo (sprites de pokémon equipados)
   - 📘 Pokédex (sprites de especies vistas)

**Nota**: Si no hay imagen, se mostrará un emoji como fallback.

Consulta `img/INSTRUCCIONES_IMAGENES.md` para más detalles.

## Probar los endpoints con sesión (curl / PowerShell)

Los endpoints requieren la cookie de sesión PHP (`PHPSESSID`). Forma simple de probarlos:

1) Inicia sesión en la web con el navegador y copia el valor de la cookie `PHPSESSID` (DevTools → Application → Cookies).

2) Usa curl (ejemplo en Git Bash / WSL):
```bash
curl -b "PHPSESSID=TU_COOKIE_AQUI" -H "Content-Type: application/json" \
  -d '{"item_id":1}' http://localhost/DAW_EJERCICIOS/Pokemonrol/api/use_item.php
```

3) O en PowerShell usando `Invoke-RestMethod` (menos directo si cookie no está en formato correcto):
```powershell
 $cookies = @{ PHPSESSID = 'TU_COOKIE_AQUI' }
 Invoke-RestMethod -Uri http://localhost/DAW_EJERCICIOS/Pokemonrol/api/get_inventory.php -WebSession (New-Object Microsoft.PowerShell.Commands.WebRequestSession)
```

Nota: es más simple probar desde la UI (F12 → Network) porque el navegador ya adjunta la cookie de sesión.

## Comprobaciones y debugging

- Si recibes errores SQL de columna inexistente, ejecuta la migración `003` o `004` para añadir las columnas.
- Revisa `C:\xampp\apache\logs\error.log` para errores PHP/Apache.
- Abre la consola del navegador (F12 → Console / Network) para ver respuestas JSON y errores JS.

## Siguientes pasos recomendados

- Ejecutar `004-create-fresh-db.sql` para empezar con una base limpia (si tu base actual da problemas).
- Probar el flujo en `dashboard.php`: Inventario → Usar poción → seleccionar Pokémon → ver efecto.
- Si quieres, puedo añadir tests curl/PowerShell completos que validen todos los endpoints y te den un reporte.

---

Si necesitas que genere el hash de la contraseña y actualice el script `004` con ese hash, o que prepare los comandos curl/PowerShell automáticos para probar todos los endpoints, dímelo y lo hago.

