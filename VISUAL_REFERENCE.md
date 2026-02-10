# 🎮 SISTEMA DE ESTADÍSTICAS - VISUAL RÁPIDO

## 📊 EL ROMBO DE STATS

```
                    ↑ ATQ
                   /   \
                  /     \
            HP ←         → DEF
                  \     /
                   \   /
           ESP.DEF ← V → ESP.ATQ
                  VEL
```

**Cómo funciona:**
- Cada punto = 1 stat del Pokémon
- El tamaño aumenta con el valor del stat
- Se llena en amarillo dorado
- El centro muestra la velocidad

---

## 🎭 NATURALEZA

**Ejemplo: Audaz**
```
Naturaleza: Audaz
↑ Ataque (+10%)      ✓ Aumenta daño
↓ Esp. Ataque (-10%) ✗ Reduce hechizos
```

**Todas las naturalezas:**
```
Adamantina   (Def ↑, Esp.Atq ↓)    Arisca     (Vel ↑, Def ↓)
Audaz        (Atq ↑, Esp.Atq ↓)    Calmada    (Esp.Def ↑, Vel ↓)
Floja        (Esp.Atq ↑, Def ↓)    Miedosa    (Esp.Def ↑, Atq ↓)
Grosera      (Atq ↑, Esp.Def ↓)    Modesta    (Esp.Atq ↑, Atq ↓)
Tímida       (Vel ↑, Atq ↓)         ... y más
```

---

## ⚡ HABILIDAD

**Ejemplo: Intimidación**
```
⚡ Habilidad: Intimidación
"Reduce el ataque del enemigo al entrar en batalla"
```

**Las 10 de ejemplo:**
- Estática - Paraliza al atacante
- Sintonía - Copia el tipo del ataque
- Torrente - Aumenta agua cuando bajo HP
- Intimidación - Reduce ataque enemigo
- ... y más

---

## 🎯 MOVIMIENTOS

**Una tarjeta de movimiento:**
```
┌─────────────────────────────────────────┐
│ 1  [Fuego]  Puño Fuego                  │
│           Potencia: 75  Precisión: 100% │
│           PP: 15/15                     │
│           [Olvidar]                     │
└─────────────────────────────────────────┘
```

**Datos de movimiento:**
- **Slot**: 1-4 (máximo 4 movimientos)
- **Tipo**: Color diferente por tipo (Rojo=Fuego, Azul=Agua)
- **Potencia**: Daño base (0 si es movimiento de estado)
- **Precisión**: Probabilidad de acertar (%)
- **PP**: Power Points (cuántas veces se puede usar)
- **Categoría**: Físico, Especial, o Estado

---

## 📚 APRENDER MOVIMIENTOS

**Movimientos disponibles:**
```
┌──────────────────────────────────────┐
│ 📚 Movimientos disponibles (15)       │
├──────────────────────────────────────┤
│ Puño Fuego           Nv. 10 [Enseñar]│
│ Rueda de Fuego       Nv. 15 [Enseñar]│
│ Recuperación         Nv. 7  [Enseñar]│
│ ...                                  │
└──────────────────────────────────────┘
```

**Cómo enseñar:**
1. Abre modal "ℹ️ Info" de un Pokémon
2. Desplázate a "Movimientos disponibles"
3. Haz click en "Enseñar" en el movimiento deseado
4. Se añade automáticamente al primer slot vacío

---

## 📋 FLUJO COMPLETO

```
┌─────────────────────────────────────────────────────┐
│  Dashboard - Caja Pokémon                           │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌────────────────────────────────────────────┐    │
│  │ 🟨 Bulbasaur (apodo)                   │    │
│  │ Nivel 5                                │    │
│  │                                        │    │
│  │ [Mover] [Enviar] [Marcar] [ℹ️ Info] │ ← CLICK
│  └────────────────────────────────────────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│ Modal: Bulbasaur                                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  [Sprite]                                          │
│  Bulbasaur - Nivel 5 - 30/30 HP           │
│                                                     │
│  ┌──────────────────────────────────────┐         │
│  │         ↑ ATQ: 12                    │         │
│  │       /       \                      │         │
│  │      /         \                     │         │
│  │   HP: 11      DEF: 11                │         │
│  │      \         /                     │         │
│  │       \       /                      │         │
│  │    ESP.DEF   ESP.ATQ: 14             │         │
│  │       VEL: 10                        │         │
│  └──────────────────────────────────────┘         │
│                                                     │
│  🎭 Naturaleza: Audaz                             │
│     ↑ Ataque (+10%)   ↓ Esp. Ataque (-10%)       │
│                                                     │
│  ⚡ Habilidad: Estática                           │
│     "Puede paralizar al atacante"                 │
│                                                     │
│  🎯 Movimientos (2/4)                            │
│  ┌──────────────────────────────────────┐        │
│  │ 1 [Normal] Placaje                   │        │
│  │           Potencia: 40  Precisión: 100%       │
│  │           PP: 15/15         [Olvidar]│        │
│  └──────────────────────────────────────┘        │
│  ┌──────────────────────────────────────┐        │
│  │ 2 [Normal] Danza Espada              │        │
│  │           Estado      PP: 20/20      │        │
│  │           [Olvidar]                  │        │
│  └──────────────────────────────────────┘        │
│                                                     │
│  📚 Disponibles (3)                              │
│  ┌──────────────────────────────────────┐        │
│  │ Puño Fuego - Nv. 10      [Enseñar]  │        │
│  │ Rayo Hielo - Nv. 15      [Enseñar]  │        │
│  │ Síntesis   - Nv. 13      [Enseñar]  │        │
│  └──────────────────────────────────────┘        │
│                                    [Cerrar]       │
└─────────────────────────────────────────────────────┘
```

---

## 🗄️ BASE DE DATOS - ESTRUCTURA SIMPLIFICADA

```
tipos
├── Normal (color blanco)
├── Fuego (color rojo)
├── Agua (color azul)
└── ... 15 tipos más

naturalezas
├── Audaz → +Ataque, -Esp.Ataque
├── Arisca → +Velocidad, -Defensa
└── ... 23 naturalezas más

habilidades
├── Intimidación
├── Torrente
└── ... 8 habilidades más

movimientos
├── Puño Fuego (Fuego/Físico, Pot:75, Prec:100)
├── Rayo Hielo (Hielo/Especial, Pot:90, Prec:100)
└── ... 13 movimientos más

pokemon_box (datos del Pokémon capturado)
├── id, user_id, species_id
├── apodo, nivel
├── hp, max_hp, status
├── naturaleza_id ← ¡NUEVO!
└── habilidad_id ← ¡NUEVO!

pokemon_movimiento (relación N:M) ← ¡NUEVO!
├── pokemon_box_id
├── movimiento_id
├── slot (1-4)
└── pp_actual

pokemon_species_movimiento ← ¡NUEVO!
├── species_id
├── movimiento_id
└── nivel (cuándo aprende)
```

---

## 🚀 PASOS PARA ACTIVAR

**1. Ejecutar migración:**
```bash
mysql -u root -p rol < migrations/009-add-pokemon-stats-system.sql
```

**2. Abrir dashboard:**
```
http://localhost/DAW_EJERCICIOS/Pokemonrol/dashboard.php
```

**3. Hacer click en "ℹ️ Info"**
```
Pestaña: 📦 Caja Pokémon
Buscar: botón "ℹ️ Info"
Click: ¡Se abre el modal!
```

---

## 🎓 REFERENCIA RÁPIDA

| Elemento | Archivo | Descripción |
|----------|---------|-------------|
| Migración SQL | `migrations/009-*` | Crea tablas + datos |
| API Información | `api/get_pokemon_info.php` | GET datos Pokémon |
| API Movimientos | `api/learn_move.php` | POST enseñar/olvidar |
| JavaScript | `scripts/pokemon-info.js` | Modal + rombo |
| Estilos | `style.css` | CSS nuevo (400+ líneas) |
| Dashboard | `dashboard.php` | Botones + modal |
| Guía Completa | `STATS_SYSTEM_GUIDE.md` | Documentación detallada |

---

## ✨ LO BONITO DE ESTO

- 🎨 Interfaz hermosa y moderna
- 📊 Visualización intuitiva (rombo de stats)
- 🔧 Completamente funcional
- 📚 Sistema extensible
- 🛡️ Seguro (prepared statements)
- 🎯 Datos realistas (naturalezas/habilidades reales)
- 📱 Responsive (funciona en móvil)

---

**¡Listo para usar! 🎮**
