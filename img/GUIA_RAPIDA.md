# 🎨 Guía Rápida de Imágenes

## En 3 pasos

### 1️⃣ Coloca las imágenes
```
img/
├── pokemon/
│   ├── bulbasaur.png
│   ├── charmander.png
│   └── ...
└── items/
    ├── potion.png
    ├── great-ball.png
    └── ...
```

### 2️⃣ Actualiza la BD
```sql
UPDATE pokemon_species SET sprite = 'bulbasaur.png' WHERE nombre = 'Bulbasaur';
UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
```

### 3️⃣ ¡Listo!
Las imágenes aparecerán automáticamente en:
- Inventario 🎒
- Caja Pokémon 📦
- Equipo ⚔️
- Pokédex 📘

---

## Dónde aparecen las imágenes

| Sección | Campo BD | Carpeta | Size |
|---------|----------|---------|------|
| Inventario | `items.icono` | `img/items/` | 56x56 |
| Caja | `pokemon_species.sprite` | `img/pokemon/` | 72x72 |
| Equipo | `pokemon_species.sprite` | `img/pokemon/` | 48x48 |
| Pokédex | `pokemon_species.sprite` | `img/pokemon/` | 40x40 |

---

## Ejemplos de comandos SQL

### Llenar sprites automáticamente (nombres en minúsculas)
```sql
UPDATE pokemon_species SET sprite = CONCAT(LOWER(nombre), '.png') WHERE sprite IS NULL OR sprite = '';
```

### Llenar por ID
```sql
UPDATE pokemon_species SET sprite = CONCAT('pokemon_', id, '.png') WHERE sprite IS NULL OR sprite = '';
```

### Actualizar un pokémon específico
```sql
UPDATE pokemon_species SET sprite = 'pikachu.png' WHERE nombre = 'Pikachu';
```

### Actualizar un item específico
```sql
UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
```

---

## Dónde conseguir imágenes

### Pokémon Sprites (96x96 o 256x256)
- [Bulbapedia](https://bulbapedia.bulbagarden.net)
- [PokéAPI](https://pokeapi.co)
- [Spriters Resource](https://www.spriters-resource.com)

### Items/Objetos (48x48 o 64x64)
- [Bulbapedia](https://bulbapedia.bulbagarden.net)
- [Pokemon Database](https://pokemondb.net)

---

## Verificar que funciona

Abre en el navegador:
```
http://localhost/DAW_EJERCICIOS/Pokemonrol/test-images.html
```

Te mostrará:
- ✓ Imágenes encontradas
- ✗ Imágenes faltantes

---

## Si algo no se ve

1. Verifica el nombre exacto en la BD
   ```sql
   SELECT sprite FROM pokemon_species WHERE id = 1;
   SELECT icono FROM items WHERE id = 1;
   ```

2. Verifica que el archivo existe en la carpeta correcta

3. Abre la consola del navegador (F12) y busca errores de red (404)

4. Intenta añadir una imagen de prueba simple:
   - Descarga un PNG de prueba
   - Colócalo en `img/pokemon/test.png`
   - Actualiza la BD: `UPDATE pokemon_species SET sprite = 'test.png' WHERE id = 1;`
   - Recarga el dashboard

---

## Notas importantes

✓ Las imágenes se centran automáticamente  
✓ Se escalan proporcionalmente  
✓ Los emojis aparecen si no hay imagen (fallback)  
✓ Compatible con todos los navegadores  
✓ No necesitas cambiar código, solo añadir imágenes y actualizar la BD

---

**¿Preguntas?** Consulta:
- `img/INSTRUCCIONES_IMAGENES.md` - Guía detallada
- `img/EJEMPLO_NOMBRES.md` - Ejemplos de nombres
- `test-images.html` - Verificar rutas
- `CAMBIOS_IMAGENES.md` - Resumen técnico
