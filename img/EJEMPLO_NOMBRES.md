# Ejemplo de nombres de archivos

## Pokémon (sprites)
Coloca estos en `img/pokemon/`:

```
bulbasaur.png
ivysaur.png
venusaur.png
charmander.png
charmeleon.png
charizard.png
squirtle.png
wartortle.png
blastoise.png
pikachu.png
raichu.png
```

## Objetos/Items (iconos)
Coloca estos en `img/items/`:

```
potion.png
great-ball.png
super-potion.png
full-restore.png
antidote.png
awakening.png
burn-heal.png
ice-heal.png
full-heal.png
full-revive.png
revive.png
rare-candy.png
```

## Cómo actualizar la BD con estos nombres

Si tus imágenes tienen estos nombres, ejecuta esta migración:

```sql
-- Actualizar pokemon_species
UPDATE pokemon_species SET sprite = CONCAT(LOWER(nombre), '.png') WHERE sprite IS NULL OR sprite = '';

-- Actualizar items (ejemplo)
UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
UPDATE items SET icono = 'great-ball.png' WHERE nombre = 'Gran Bola';
UPDATE items SET icono = 'super-potion.png' WHERE nombre = 'Superpoción';
```

## Consejos para encontrar imágenes

1. **Para Pokémon sprites:**
   - Sitios: Bulbapedia, PokéAPI, Spriters Resource
   - Busca: "pokemon [nombre] sprite png" en Google Images
   - Ideal: 96x96 o 256x256 con fondo transparente

2. **Para objetos/items:**
   - Sitios: Bulbapedia, Pokemon Database
   - Busca: "pokemon [item] icon png" en Google Images
   - Ideal: 48x48 o 64x64 con fondo transparente

3. **Herramientas útiles:**
   - ImageMagick: para cambiar nombres en lote
   - XnConvert: para convertir entre formatos en lote
