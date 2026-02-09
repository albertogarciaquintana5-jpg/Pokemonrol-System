# ✅ Checklist de Implementación de Imágenes

## Estado: COMPLETADO ✓

---

## Qué se ha hecho

### ✅ Backend (PHP)
- [x] Actualizadas queries en `dashboard.php` para incluir campos de imagen
- [x] Actualizado API `get_team.php` para devolver `sprite`
- [x] APIs `get_box.php` y `get_inventory.php` ya devolvían imágenes (sin cambios)
- [x] HTML modificado para mostrar imágenes con fallback a emoji

### ✅ Frontend (HTML/CSS)
- [x] Inventario: muestra `items.icono`
- [x] Caja Pokémon: muestra `pokemon_species.sprite`
- [x] Equipo Pokémon: muestra `pokemon_species.sprite`
- [x] Pokédex: muestra `pokemon_species.sprite`
- [x] Estilos CSS añadidos para todas las imágenes
- [x] Fallback a emoji si no hay imagen

### ✅ JavaScript
- [x] Función `renderTeam()` actualizada para mostrar imágenes dinámicamente
- [x] Manejo seguro de innerHTML

### ✅ Carpetas
- [x] Creada `img/pokemon/` para sprites
- [x] Creada `img/items/` para iconos

### ✅ Documentación
- [x] `img/GUIA_RAPIDA.md` - Guía de 3 pasos
- [x] `img/INSTRUCCIONES_IMAGENES.md` - Guía completa
- [x] `img/EJEMPLO_NOMBRES.md` - Ejemplos
- [x] `test-images.html` - Verificador de rutas
- [x] `CAMBIOS_IMAGENES.md` - Resumen
- [x] `CAMBIOS_IMAGENES_DETALLADOS.md` - Cambios técnicos

---

## Lo que DEBES hacer tú

### 1. Descarga las imágenes
- [ ] Busca sprites de Pokémon (PNG, 96x96 o 256x256)
- [ ] Busca iconos de items (PNG, 48x48 o 64x64)
- [ ] Sitios recomendados:
  - Pokémon sprites: Bulbapedia, PokéAPI, Spriters Resource
  - Items: Bulbapedia, Pokemon Database

### 2. Organiza los archivos
- [ ] Coloca sprites de Pokémon en `img/pokemon/`
  - Ejemplo: `bulbasaur.png`, `charmander.png`, `pikachu.png`
- [ ] Coloca iconos en `img/items/`
  - Ejemplo: `potion.png`, `great-ball.png`, `super-potion.png`

### 3. Actualiza la BD
- [ ] Abre una conexión MySQL
- [ ] Ejecuta comandos UPDATE para llenar `sprite` e `icono`:

```sql
-- Opción A: Si tus archivos se llaman como el nombre en minúsculas
UPDATE pokemon_species SET sprite = CONCAT(LOWER(nombre), '.png');
UPDATE items SET icono = CONCAT(LOWER(nombre), '.png');

-- Opción B: Si tus archivos se llaman por ID
UPDATE pokemon_species SET sprite = CONCAT(id, '.png');
UPDATE items SET icono = CONCAT(id, '.png');

-- Opción C: Individual (si tienes nombres específicos)
UPDATE pokemon_species SET sprite = 'bulbasaur.png' WHERE nombre = 'Bulbasaur';
UPDATE items SET icono = 'potion.png' WHERE nombre = 'Poción';
```

### 4. Verifica que funciona
- [ ] Abre `http://localhost/DAW_EJERCICIOS/Pokemonrol/test-images.html`
- [ ] Deberías ver ✓ Existe junto a las imágenes
- [ ] Si ves ✗ No encontrada, verifica el nombre del archivo

### 5. Abre el dashboard
- [ ] Abre `http://localhost/DAW_EJERCICIOS/Pokemonrol/dashboard.php`
- [ ] Deberías ver las imágenes en lugar de emojis
- [ ] Si ves emojis, verifica:
  - Que los archivos están en las carpetas correctas
  - Que el nombre en la BD coincide con el del archivo
  - F12 Network tab para ver errores HTTP 404

---

## Checklist de Verificación

### Carpetas
- [ ] `img/pokemon/` existe
- [ ] `img/items/` existe
- [ ] Tengo archivos en `img/pokemon/`
- [ ] Tengo archivos en `img/items/`

### Base de Datos
- [ ] `pokemon_species.sprite` tiene valores
- [ ] `items.icono` tiene valores
- [ ] Los nombres coinciden con los archivos
- [ ] Sin espacios en blanco extra

### Código
- [ ] No hay cambios necesarios (ya están hechos)
- [ ] Las imágenes se muestran en dashboard.php
- [ ] test-images.html muestra rutas correctas

### Documentación
- [ ] Leí `img/GUIA_RAPIDA.md`
- [ ] Leí `img/INSTRUCCIONES_IMAGENES.md`
- [ ] Entiendo dónde colocar las imágenes
- [ ] Entiendo cómo actualizar la BD

---

## Estructura Final Esperada

```
img/
├── pokemon/
│   ├── bulbasaur.png
│   ├── charmander.png
│   ├── squirtle.png
│   ├── pikachu.png
│   └── ... (más pokémon)
├── items/
│   ├── potion.png
│   ├── great-ball.png
│   ├── super-potion.png
│   └── ... (más items)
├── GUIA_RAPIDA.md
├── INSTRUCCIONES_IMAGENES.md
└── EJEMPLO_NOMBRES.md
```

---

## Troubleshooting

### ❓ Las imágenes no aparecen

Sigue este orden:

1. **Verifica que el archivo existe**
   ```bash
   ls -la img/pokemon/
   ls -la img/items/
   ```

2. **Verifica el nombre en la BD**
   ```sql
   SELECT nombre, sprite FROM pokemon_species WHERE id = 1;
   SELECT nombre, icono FROM items WHERE id = 1;
   ```

3. **Abre F12 Network** y busca:
   - Requests a `img/pokemon/...`
   - Código 404 = archivo no existe
   - Código 200 = funciona ✓

4. **Usa test-images.html**
   - Abre `http://localhost/DAW_EJERCICIOS/Pokemonrol/test-images.html`
   - Te mostrará exactamente qué falta

### ❓ Aparecen emojis en lugar de imágenes

Significa que:
- El campo `sprite` o `icono` está vacío en la BD, O
- El archivo no existe en la carpeta

**Solución:**
1. Verifica que actualizaste la BD
2. Verifica que los archivos están en la carpeta correcta
3. Verifica que los nombres coinciden (mayúsculas/minúsculas importa)

### ❓ El nombre en la BD no coincide con el archivo

**Solución rápida:**
```bash
# En PowerShell, ve a img/pokemon/ y lista los archivos
cd img\pokemon
Get-ChildItem | Select Name
```

Luego actualiza la BD para que coincidan los nombres.

---

## Notas Importantes

- 📌 Las mayúsculas/minúsculas importan: `Bulbasaur.png` ≠ `bulbasaur.png`
- 📌 Sin espacios al final: `pikachu.png ` (con espacio) no funcionará
- 📌 Usa `.png` o `.jpg`, no `.PNG` o `.JPG` (caso sensible en Linux)
- 📌 Los emojis siguen apareciendo si no hay imagen (eso es normal)

---

## ¿Todo hecho?

Si respondiste SÍ a todas estas preguntas:
- [ ] Las imágenes están en las carpetas correctas
- [ ] La BD tiene los nombres correctos
- [ ] test-images.html muestra ✓ Existe
- [ ] dashboard.php muestra las imágenes

**¡FELICIDADES!** 🎉 Tu Pokémon Rol ahora tiene imágenes.

---

## Próximas mejoras (opcionales)

- [ ] Añadir más pokémon/items
- [ ] Usar imágenes en mejor calidad
- [ ] Crear sprites animados (GIF)
- [ ] Agregar efectos CSS (hover, etc.)
- [ ] Integrar PokéAPI para descargar imágenes automáticamente

---

**¿Necesitas ayuda?**

Consulta:
1. `img/GUIA_RAPIDA.md` - Lo esencial en 5 minutos
2. `img/INSTRUCCIONES_IMAGENES.md` - Guía completa
3. `test-images.html` - Verificar qué falta
4. `CAMBIOS_IMAGENES_DETALLADOS.md` - Cambios técnicos

---

**Última actualización:** 2 de diciembre de 2025
**Estado:** ✅ IMPLEMENTACIÓN COMPLETADA

**Ahora te toca a ti: ¡Descarga las imágenes y actualiza la BD!** 🚀
