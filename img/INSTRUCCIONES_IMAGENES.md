# Instrucciones para añadir imágenes

## Estructura de carpetas

```
img/
├── pokemon/     (Imágenes de Pokémon)
└── items/       (Imágenes de objetos/items)
```

## Cómo funcionan las imágenes

El sistema está configurado para mostrar imágenes automáticamente según los campos en la base de datos:

### Pokémon (Sprite)
- **Campo en BD**: `pokemon_species.sprite`
- **Carpeta**: `/img/pokemon/`
- **Ejemplo**: Si `sprite = "bulbasaur.png"`, la imagen se cargará desde `img/pokemon/bulbasaur.png`

### Objetos/Items (Icono)
- **Campo en BD**: `items.icono`
- **Carpeta**: `/img/items/`
- **Ejemplo**: Si `icono = "potion.png"`, la imagen se cargará desde `img/items/potion.png`

## Pasos para añadir tus imágenes

1. **Descarga o crea tus imágenes**
   - Pokémon: busca en Google "pokemon sprite png" (formato PNG con fondo transparente es ideal)
   - Objetos: busca "pokemon item icon png"

2. **Nombra las imágenes correctamente**
   - El nombre debe coincidir con el valor en la base de datos
   - Ejemplo: Si en `pokemon_species.sprite` está "pikachu.png", coloca un archivo "pikachu.png"

3. **Coloca los archivos en las carpetas correctas**
   - Pokémon → `/img/pokemon/`
   - Objetos → `/img/items/`

4. **Actualiza la BD si es necesario**
   - Si tus imágenes tienen nombres diferentes, actualiza los valores en la BD:
   ```sql
   UPDATE pokemon_species SET sprite = 'nombre-imagen.png' WHERE id = 1;
   UPDATE items SET icono = 'nombre-imagen.png' WHERE id = 1;
   ```

## Recomendaciones

- **Tamaño**: 128x128 o 256x256 px para Pokémon (pueden ser más grandes, se ajustarán)
- **Formato**: PNG con fondo transparente es lo mejor
- **Nombres**: Usa minúsculas, guiones en lugar de espacios (ej: `red-potion.png`)

## Notas técnicas

- Las imágenes se centran automáticamente en sus contenedores
- Se escalan proporcionalmente para ocupar el espacio disponible
- Si no hay imagen en la BD, se muestra un emoji como fallback

## Ejemplo de migración SQL para cargar imágenes

Si tienes un lote de datos y sus imágenes:

```sql
-- Actualizar sprites de pokemon
UPDATE pokemon_species SET sprite = 'bulbasaur.png' WHERE nombre = 'Bulbasaur';
UPDATE pokemon_species SET sprite = 'ivysaur.png' WHERE nombre = 'Ivysaur';

-- Actualizar iconos de items
UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
UPDATE items SET icono = 'great-ball.png' WHERE nombre = 'Gran Bola';
```

¡Listo! Las imágenes aparecerán automáticamente en:
- 🎒 Inventario (con íconos de items)
- 📦 Caja Pokémon (con sprites de Pokémon)
- ⚔️ Equipo (con sprites de Pokémon equipados)
- 📘 Pokédex (con sprites de especies vistas)
