# 📚 SISTEMA DE APRENDIZAJE DE MOVIMIENTOS

## 🎯 Descripción

Sistema implementado que permite enseñar movimientos a Pokémon con **restricción por nivel**. Los Pokémon solo pueden aprender movimientos si su nivel es igual o superior al nivel requerido del movimiento.

---

## ✨ Características

### Validaciones Implementadas:

1. ✅ **Nivel Requerido**: El Pokémon debe tener nivel suficiente
2. ✅ **Máximo de Movimientos**: Límite de 4 movimientos por Pokémon
3. ✅ **Movimientos Únicos**: No se puede aprender el mismo movimiento dos veces
4. ✅ **Verificación de Existencia**: Pokémon y movimiento deben existir

### Mensajes de Error:

- ❌ "El Pokémon necesita nivel X para aprender [Movimiento] (nivel actual: Y)"
- ❌ "El Pokémon ya tiene 4 movimientos (máximo permitido)"
- ❌ "El Pokémon ya conoce este movimiento"

---

## 🎮 Cómo Usar (Panel Admin)

### Paso 1: Acceder al Panel
1. Inicia sesión como usuario ID 67
2. Ve al Panel de Administrador

### Paso 2: Seleccionar Pokémon
1. Haz clic en un jugador de la lista
2. Verás su equipo y caja de Pokémon
3. Cada Pokémon tiene botones "Editar" y "**Enseñar**"

### Paso 3: Enseñar Movimiento
1. Haz clic en el botón "**Enseñar**" (📚)
2. Se abre un modal mostrando:
   - Nombre del Pokémon
   - Nivel actual del Pokémon
   - Lista de movimientos disponibles
3. Los movimientos se muestran como:
   - `Nombre (Nv.X) - PP:Y - POT:Z`
4. Movimientos bloqueados aparecen en gris con "**BLOQUEADO**"

### Paso 4: Confirmar
1. Selecciona un movimiento desbloqueado
2. Haz clic en "**Enseñar Movimiento**"
3. Confirma la acción
4. ✅ El movimiento se añade automáticamente

---

## 📊 Niveles de Movimientos

### Movimientos Básicos (Nivel 1)
- Ataque Rápido
- Placaje
- Bofetón Lodo
- Puño Fuego
- Protección
- Recuperación
- Síntesis
- Rueda de Fuego

### Movimientos Intermedios (Nivel 7-10)
- **Nv.7**: Danza Espada, Defensa Férrea
- **Nv.10**: Rayo, Rayo Hielo

### Movimientos Avanzados (Nivel 20+)
- **Nv.20**: Destello Espectral
- **Nv.30**: Terremoto

---

## 🔧 Estructura Técnica

### Base de Datos

**Tabla: movimientos**
```sql
id INT
nombre VARCHAR(100)
tipo_id INT
categoria ENUM('fisico','especial','estado')
potencia INT
precision INT
pp INT
nivel_requerido INT  ← NUEVA COLUMNA
descripcion TEXT
```

**Tabla: pokemon_movimiento** (sin cambios)
```sql
id INT
pokemon_box_id INT
movimiento_id INT
pp_actual INT
```

### API: admin_teach_move.php

**Endpoint:** `POST /api/admin_teach_move.php`

**Request:**
```json
{
  "pokemon_box_id": 5,
  "movimiento_id": 10
}
```

**Response Éxito:**
```json
{
  "success": true,
  "message": "¡Danza Espada aprendido correctamente!",
  "movimiento": "Danza Espada",
  "movimientos_totales": 3
}
```

**Response Error (Nivel Insuficiente):**
```json
{
  "success": false,
  "error": "El Pokémon necesita nivel 7 para aprender Danza Espada (nivel actual: 5)",
  "nivel_requerido": 7,
  "nivel_actual": 5
}
```

**Response Error (Máximo Alcanzado):**
```json
{
  "success": false,
  "error": "El Pokémon ya tiene 4 movimientos (máximo permitido)"
}
```

---

## 🎨 Interfaz de Usuario

### Botón "Enseñar"
- **Ubicación**: Junto al botón "Editar" en cada Pokémon
- **Icono**: 📚 (Bootstrap Icon: bi-book)
- **Color**: Verde (btn-outline-success)

### Modal "Enseñar Movimiento"
- **Header**: Título + botón cerrar
- **Body**:
  - Alert info con nombre y nivel del Pokémon
  - Select de movimientos (size=10 para scroll)
  - Movimientos bloqueados en gris
- **Footer**:
  - Botón Cancelar (gris)
  - Botón "Enseñar Movimiento" (azul)

### Lista de Movimientos
```
Formato: Nombre (Nv.X) - PP:Y - POT:Z

Ejemplo:
✓ Placaje (Nv.1) - PP:35 - POT:40
✓ Danza Espada (Nv.7) - PP:20 - POT:0
⛔ Terremoto (Nv.30 - BLOQUEADO)  ← Si nivel < 30
```

---

## 🧪 Testing

### Caso 1: Nivel Suficiente
```
Pokémon: Pikachu (Nivel 10)
Movimiento: Rayo (Nv.10)
Resultado: ✅ Éxito - "¡Rayo aprendido correctamente!"
```

### Caso 2: Nivel Insuficiente
```
Pokémon: Pikachu (Nivel 5)
Movimiento: Terremoto (Nv.30)
Resultado: ❌ Error - "Necesita nivel 30 (actual: 5)"
```

### Caso 3: Máximo de Movimientos
```
Pokémon: Pikachu (4 movimientos)
Movimiento: Rayo (Nv.10)
Resultado: ❌ Error - "Ya tiene 4 movimientos"
```

### Caso 4: Movimiento Duplicado
```
Pokémon: Pikachu (ya conoce Placaje)
Movimiento: Placaje (Nv.1)
Resultado: ❌ Error - "Ya conoce este movimiento"
```

---

## 📝 Migración SQL

**Archivo:** `migrations/add_nivel_requerido_movimientos.sql`

Ejecutado automáticamente, incluye:
1. Agregar columna `nivel_requerido`
2. Establecer valores por defecto
3. Actualizar movimientos existentes con niveles apropiados

---

## 🔐 Seguridad

### Validaciones Backend:
- ✅ Autenticación (solo ID 67)
- ✅ Prepared statements
- ✅ Validación de tipos (parseInt)
- ✅ Verificación de existencia
- ✅ Límite de movimientos
- ✅ Output buffering

### Validaciones Frontend:
- ✅ Deshabilitar opciones bloqueadas
- ✅ Visual feedback (gris + texto)
- ✅ Confirmación antes de enseñar
- ✅ Mensajes de error claros

---

## 🚀 Flujo Completo

```
1. Admin selecciona jugador
   ↓
2. Ve lista de Pokémon
   ↓
3. Clic en "Enseñar" (Pikachu Nv.5)
   ↓
4. Modal muestra movimientos:
   - Placaje (Nv.1) ✓
   - Danza Espada (Nv.7) ⛔ BLOQUEADO
   - Terremoto (Nv.30) ⛔ BLOQUEADO
   ↓
5. Selecciona "Placaje"
   ↓
6. Confirma acción
   ↓
7. API valida:
   - ✓ Nivel suficiente (5 >= 1)
   - ✓ No tiene 4 movimientos
   - ✓ No conoce Placaje
   ↓
8. INSERT en pokemon_movimiento
   ↓
9. ✅ "¡Placaje aprendido correctamente!"
   ↓
10. Vista se actualiza automáticamente
```

---

## 💡 Tips

### Para el Master:
- Los movimientos de Nv.1 son accesibles para todos los Pokémon
- Movimientos poderosos requieren nivel alto
- Puedes ver el nivel requerido en el selector antes de intentar enseñar
- El sistema previene errores automáticamente

### Para Desarrolladores:
- Agregar nuevos movimientos: Incluye `nivel_requerido` en INSERT
- Modificar niveles: UPDATE movimientos SET nivel_requerido = X WHERE id = Y
- La columna tiene DEFAULT 1, por lo que es retrocompatible

---

## 📚 Archivos Modificados/Creados

### Nuevos:
1. `api/admin_teach_move.php` - API de enseñanza
2. `migrations/add_nivel_requerido_movimientos.sql` - Migración BD
3. Este documento

### Modificados:
1. `admin.php`:
   - Modal "Enseñar Movimiento"
   - Botón "Enseñar" en cada Pokémon
   - Funciones JS: showTeachMoveModal(), teachMove()
   - Query de movimientos incluye nivel_requerido

---

## ✅ Checklist de Implementación

- [x] Columna nivel_requerido agregada
- [x] Migración SQL ejecutada
- [x] Niveles asignados a movimientos
- [x] API admin_teach_move.php creada
- [x] Validación de nivel implementada
- [x] Validación de máximo 4 movimientos
- [x] Modal UI agregado
- [x] Botón "Enseñar" agregado
- [x] JavaScript funcional
- [x] Movimientos bloqueados en gris
- [x] Actualización automática sin F5
- [x] Mensajes de error claros
- [x] Testing completado

---

**Sistema 100% funcional** ✅

Los Pokémon ahora aprenden movimientos de forma realista, respetando sus niveles como en los juegos oficiales de Pokémon.
