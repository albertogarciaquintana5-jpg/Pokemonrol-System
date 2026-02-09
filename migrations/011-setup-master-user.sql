-- Script para configurar el usuario Master (ID 67)
-- Uso: mysql -u root -p rol < migrations/011-setup-master-user.sql

USE `rol`;

-- Opción 1: Cambiar el ID de un usuario existente a 67
-- Reemplaza 'tu_correo@ejemplo.com' con tu correo real

-- IMPORTANTE: Descomenta y ajusta UNA de las siguientes opciones

-- =====================================================
-- OPCIÓN 1: Actualizar usuario existente
-- =====================================================
-- Deshabilitar temporalmente las verificaciones de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Actualizar el ID (cambia el correo por el tuyo)
UPDATE usuarios SET id = 67 WHERE correo = 'albertogarciaquintana5@gmail.com';

-- Actualizar todas las tablas relacionadas
UPDATE inventario SET user_id = 67 WHERE user_id = (SELECT @old_id := id FROM usuarios WHERE correo = 'albertogarciaquintana5@gmail.com' AND id != 67 LIMIT 1);
UPDATE pokemon_box SET user_id = 67 WHERE user_id = @old_id;
UPDATE team SET user_id = 67 WHERE user_id = @old_id;
UPDATE pokedex SET user_id = 67 WHERE user_id = @old_id;

-- Volver a habilitar las verificaciones
SET FOREIGN_KEY_CHECKS = 1;
Método más simple - Intercambiar IDs
-- =====================================================
-- Si tienes problemas con el método anterior, usa este:

-- 1. Primero guarda el ID actual de tu usuario
SET @mi_id = (SELECT id FROM usuarios WHERE correo = 'albertogarciaquintana5@gmail.com');

-- 2. Si ya existe un usuario con ID 67, intercambia los IDs
SET FOREIGN_KEY_CHECKS = 0;

-- Cambiar el usuario actual ID 67 a un ID temporal (si existe)
UPDATE usuarios SET id = 9999 WHERE id = 67 AND id != @mi_id;
UPDATE inventario SET user_id = 9999 WHERE user_id = 67;
UPDATE pokemon_box SET user_id = 9999 WHERE user_id = 67;
UPDATE team SET user_id = 9999 WHERE user_id = 67;
UPDATE pokedex SET user_id = 9999 WHERE user_id = 67;

-- Cambiar tu usuario al ID 67
UPDATE inventario SET user_id = 67 WHERE user_id = @mi_id;
UPDATE pokemon_box SET user_id = 67 WHERE user_id = @mi_id;
UPDATE team SET user_id = 67 WHERE user_id = @mi_id;
UPDATE pokedex SET user_id = 67 WHERE user_id = @mi_id;
UPDATE usuarios SET id = 67 WHERE id = @mi_id;

-- Cambiar el usuario temporal de vuelta al ID original
UPDATE inventario SET user_id = @mi_id WHERE user_id = 9999;
UPDATE pokemon_box SET user_id = @mi_id WHERE user_id = 9999;
UPDATE team SET user_id = @mi_id WHERE user_id = 9999;
UPDATE pokedex SET user_id = @mi_id WHERE user_id = 9999;
UPDATE usuarios SET id = @mi_id WHERE id = 9999;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- OPCIÓN 4: 
-- =====================================================
-- OPCIÓN 2: Crear nuevo usuario Master con ID 67
-- =====================================================
-- Primero, asegúrate de que el ID 67 esté libre
-- DELETE FROM usuarios WHERE id = 67;

-- Luego inserta el nuevo usuario
-- La contraseña aquí es 'MasterPass2024' (cámbiala después de iniciar sesión)
-- INSERT INTO usuarios (id, nombre, apellido, correo, contraseña, money) VALUES
-- (67, 'Master', 'Game', 'master@pokemonrol.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 999999.00);

-- =====================================================
-- OPCIÓN 3: Verificar tu ID actual
-- =====================================================
-- Para ver tu ID actual, ejecuta:
SELECT id, nombre, apellido, correo FROM usuarios WHERE correo = 'albertogarciaquintana5@gmail.com';

-- Si tu ID no es 67, puedes cambiarlo manualmente o usar la Opción 1

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================
-- 1. Solo UN usuario puede tener el ID 67
-- 2. Este usuario tendrá acceso total al panel de administrador
-- 3. Después de cambiar el ID, cierra sesión y vuelve a iniciar sesión
-- 4. Para generar una nueva contraseña hash, usa PHP:
--    <?php echo password_hash('TuContraseña', PASSWORD_DEFAULT); ?>
-- 5. Guarda bien las credenciales del Master

-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================
-- Verifica que el usuario Master esté configurado correctamente:
SELECT 
    id,
    nombre,
    apellido,
    correo,
    money,
    created_at
FROM usuarios 
WHERE id = 67;

-- Resultado esperado: Una fila con tus datos de Master
