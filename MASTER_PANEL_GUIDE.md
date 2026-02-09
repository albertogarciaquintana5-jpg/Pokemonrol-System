# 🎮 Panel de Master - Guía Completa

## 🔐 Acceso

El panel de administrador está disponible **solo para el usuario con ID 67** (Master del juego de rol).

### Cómo acceder:
1. Inicia sesión con tu cuenta de Master
2. En el dashboard verás un botón **"Panel Master"** en la parte superior derecha
3. Haz clic para acceder al panel de administración

---

## 🎯 Funcionalidades del Panel

### 1. **Vista de Jugadores**
- Lista de todos los jugadores registrados (excepto el Master)
- Muestra nombre, apellido, correo y dinero actual
- Haz clic en cualquier jugador para ver sus datos completos

### 2. **Gestión de Pokémon**

#### Ver Equipo y Caja
- **Equipo**: Pokémon que el jugador tiene actualmente equipados
- **Caja**: Todos los Pokémon que el jugador posee

#### Editar Pokémon (botón "Editar")
Puedes modificar:
- ✏️ **Apodo**: Cambiar el nombre del Pokémon
- 📊 **Nivel**: Ajustar el nivel (1-100)
- ❤️ **HP Actual**: Modificar los puntos de vida actuales
- 💚 **HP Máximo**: Cambiar los HP máximos
- ⭐ **Experiencia**: Dar o quitar experiencia
- 🎯 **CP (Combat Power)**: Ajustar el poder de combate
- 🔴 **Estado**: Cambiar estado del Pokémon:
  - Normal (sin estado)
  - Envenenado (poisoned)
  - Paralizado (paralyzed)
  - Quemado (burned)
  - Congelado (frozen)
  - Dormido (sleeping)
  - Debilitado (fainted)

#### Gestión de Movimientos
- Ver todos los movimientos del Pokémon
- **Modificar PP**: Ajusta los puntos de poder (PP) actuales de cada movimiento
- Los cambios se guardan automáticamente

### 3. **Gestión de Inventario**

#### Ver Items
- Lista completa de items del jugador con cantidades
- Iconos visuales de cada item

#### Eliminar Items
- Botón de eliminar (🗑️) para quitar items del inventario

### 4. **Acciones Rápidas**

#### 🎁 Dar Pokémon
1. Haz clic en "Dar Pokémon"
2. Selecciona el jugador
3. Elige la especie
4. (Opcional) Añade un apodo
5. Define nivel y HP inicial
6. Confirma - el Pokémon aparecerá en la caja del jugador

#### 🎁 Dar Item
1. Haz clic en "Dar Item"
2. Selecciona el jugador
3. Elige el item
4. Define la cantidad
5. Confirma - se añadirá al inventario del jugador

#### 💰 Editar Dinero
- Desde la vista de un jugador, haz clic en "Editar dinero"
- Introduce la nueva cantidad
- Se actualiza inmediatamente

---

## 🎲 Casos de Uso como Master

### Escenario 1: Batalla de Rol
```
1. Un jugador sufre daño en combate
   → Edita el Pokémon → Reduce HP actual

2. El Pokémon sube de nivel
   → Edita el Pokémon → Aumenta nivel y HP máximo

3. El Pokémon usa movimientos
   → Edita el Pokémon → Reduce PP de los movimientos usados
```

### Escenario 2: Recompensas
```
1. Los jugadores completan una misión
   → Dar Item → Selecciona jugador y entrega recompensas

2. Capturan un nuevo Pokémon
   → Dar Pokémon → Añade el Pokémon capturado a su caja

3. Ganan dinero
   → Editar dinero → Actualiza el saldo del jugador
```

### Escenario 3: Estados de Combate
```
1. Un Pokémon es envenenado
   → Edita Pokémon → Estado: "Envenenado"

2. Necesita restaurar PP después de descanso
   → Edita Pokémon → Restaura PP de movimientos
```

### Escenario 4: Eventos Especiales
```
1. Regalo por evento
   → Dar Pokémon shiny o legendario especial

2. Recompensa grupal
   → Dar Items a múltiples jugadores rápidamente
```

---

## 🔧 APIs Disponibles

### Para Jugadores (Acceso Normal)
- `api/get_inventory.php` - Ver inventario
- `api/get_box.php` - Ver caja de Pokémon
- `api/get_team.php` - Ver equipo
- `api/use_item.php` - Usar items
- `api/move_pokemon.php` - Mover Pokémon entre equipo/caja

### Para el Master (Solo ID 67)
- `api/admin_get_player.php` - Ver datos completos de un jugador
- `api/admin_get_pokemon.php` - Ver datos de un Pokémon específico
- `api/admin_update_pokemon.php` - Modificar stats y PP de Pokémon
- `api/admin_give_pokemon.php` - Dar Pokémon a jugadores
- `api/admin_give_item.php` - Dar items a jugadores
- `api/admin_update_money.php` - Modificar dinero de jugadores
- `api/admin_remove_item.php` - Eliminar items del inventario

---

## 🛡️ Seguridad

### Protecciones implementadas:
✅ Solo el usuario con ID 67 puede acceder
✅ Verificación en cada endpoint API
✅ Si intentas acceder sin ser Master, redirige al dashboard
✅ Todas las operaciones requieren sesión activa
✅ Validación de datos en todas las APIs

### Si no eres el usuario ID 67:
- No verás el botón "Panel Master" en el dashboard
- Si intentas acceder directamente a `admin.php`, serás redirigido
- Las APIs de admin rechazarán tus peticiones

---

## 📝 Notas Importantes

1. **Cambios Inmediatos**: Todos los cambios se reflejan inmediatamente en el dashboard de los jugadores

2. **Movimientos**: Para que un Pokémon tenga movimientos, deben estar previamente asignados en la tabla `pokemon_movimiento`

3. **HP Máximo**: Al cambiar el nivel, considera ajustar también el HP máximo

4. **Estados**: Los estados afectan visualmente en el dashboard pero la lógica de combate debes implementarla tú

5. **Experiencia**: Puedes usar esto para trackear progreso sin necesariamente cambiar el nivel

6. **Backup**: Considera hacer backups periódicos de la base de datos antes de sesiones importantes

---

## 🚀 Mejoras Futuras Sugeridas

- [ ] Historial de cambios (log de acciones del Master)
- [ ] Asignar movimientos nuevos a Pokémon desde el panel
- [ ] Sistema de notas por jugador/Pokémon
- [ ] Bulk operations (modificar múltiples Pokémon a la vez)
- [ ] Generar reportes de sesión
- [ ] Sistema de backup automático antes de cambios importantes
- [ ] Modo "espectador" para ver stats sin poder editar
- [ ] Calendario de eventos/sesiones

---

## 🆘 Solución de Problemas

### No veo el botón "Panel Master"
- Verifica que tu ID de usuario sea exactamente 67
- Comprueba en la tabla `usuarios` de la base de datos

### Error "No autorizado" en las APIs
- Asegúrate de estar logueado como usuario ID 67
- Limpia cookies y vuelve a iniciar sesión

### Los cambios no se guardan
- Verifica que la base de datos esté activa (XAMPP MySQL)
- Comprueba los logs de error de PHP
- Verifica la consola del navegador (F12) para errores JavaScript

### No aparecen los movimientos de un Pokémon
- Los movimientos deben estar en la tabla `pokemon_movimiento`
- Usa la migración `009-add-pokemon-stats-system.sql` si no existe la tabla

---

## 🎯 Ejemplo de Sesión de Rol

```
Master: "¡Tu Pikachu usa Rayo contra el Pokémon salvaje!"
→ [Edita Pikachu] → Reduce PP del movimiento Rayo de 15 a 10

Jugador: "¡Genial! ¿Le hago daño?"
Master: "Sí, pero él contraataca. Tu Pikachu recibe 15 de daño"
→ [Edita Pikachu] → Reduce HP de 35 a 20

Jugador: "Quiero usar una Poción"
→ El jugador usa la poción desde su dashboard
→ HP de Pikachu se restaura automáticamente (+20 HP)

Master: "¡Excelente! Derrotaste al Pokémon. Ganas 50₽ y encuentras una Great Ball"
→ [Editar dinero] → Añade 50₽ al jugador
→ [Dar Item] → Entrega 1 Great Ball

Master: "Además, ¡tu Pikachu sube al nivel 6!"
→ [Edita Pikachu] → Nivel: 6, HP máximo: 40, HP actual: 40
```

---

**¡Disfruta gestionando tu mundo Pokémon! 🎮✨**
