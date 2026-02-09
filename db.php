<?php
/**
 * db.php
 * Wrapper simple para la conexión mysqli y función de autenticación.
 * Configura las constantes debajo según tu entorno y estructura de tabla.
 */

// CONFIG: ajusta según tu entorno
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'rol';
$DB_TABLE = 'usuarios'; // Ajusta al nombre real de tu tabla
$DB_PASSWORD_COLUMN = 'contraseña'; // Ajusta si tu columna se llama diferente (por ejemplo: contrasena o password)

// Crear conexión mysqli
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    // No queremos volcar credenciales ni detalles sensibles — lanza excepción con mensaje genérico
    throw new Exception('Error de conexión a la base de datos: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

/**
 * Autentica al usuario por correo y contraseña.
 * Devuelve un array con 'success' => true y 'user' => [...] en caso correcto,
 * o 'success' => false y 'error' => 'mensaje'.
 */
function auth_user_by_email(string $correo, string $password): array {
    global $mysqli, $DB_TABLE, $DB_PASSWORD_COLUMN;

    // Seguridad básica: limpiar entradas
    $correo = trim($correo);
    if ($correo === '' || $password === '') {
        return ['success' => false, 'error' => 'Correo y contraseña son requeridos.'];
    }

    // Preparamos consulta segura con parámetros
    // SELECT id, nombre, apellido, correo, `contraseña` AS password_hash
    $tableBacktick = '`' . str_replace('`', '``', $DB_TABLE) . '`';
    // Try a few password column names to be resilient to different schemas
    $passwordColumnCandidates = [ $DB_PASSWORD_COLUMN, 'contrasena', 'password', 'pass' ];
    $stmt = false;
    $usedPasswordCol = null;
    foreach ($passwordColumnCandidates as $candidate) {
        if ($candidate === null || $candidate === '') continue;
        $col = '`' . str_replace('`', '``', $candidate) . '`';
        $sql = "SELECT id, nombre, apellido, correo, {$col} AS password_hash FROM {$tableBacktick} WHERE correo = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $usedPasswordCol = $candidate;
            break;
        }
    }
    if (!$stmt) {
        return ['success' => false, 'error' => 'Fallo al preparar la consulta de usuario: revisa el nombre de tabla/columnas en db.php.'];
    }
    $stmt->bind_param('s', $correo);
    if (!$stmt->execute()) {
        $stmt->close();
        return ['success' => false, 'error' => 'Fallo en la ejecución de la consulta de usuario.'];
    }
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $stmt->close();
        return ['success' => false, 'error' => 'Usuario no encontrado.'];
    }

    $user = $res->fetch_assoc();
    $stmt->close();

    // Not checking 'autenticado' — treat as if it doesn't exist. If you use that column manually, it won't be enforced here.

    $passwordHash = $user['password_hash'] ?? null;
    // Verificamos contra hash si existe, y si no, intentamos comparar plain text
    $isValid = false;
    if ($passwordHash) {
        // Si el hash parece ser un hash de password_hash() lo comprobamos con password_verify
        if (@password_verify($password, $passwordHash)) {
            $isValid = true;
        }
    }
    // Fallback: si no había hash o la verificación falló, compararlo en claro (solo para migración/compat.)
    if (!$isValid && $passwordHash !== null && hash_equals($passwordHash, $password)) {
        $isValid = true;
    }

    if (!$isValid) {
        return ['success' => false, 'error' => 'Contraseña incorrecta.'];
    }

    // Remover la contraseña del array por seguridad
    unset($user['password_hash']);

    return ['success' => true, 'user' => $user];
}

?>