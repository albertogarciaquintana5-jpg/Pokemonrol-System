<?php
session_start();
// Procesa el registro y añade al usuario a la tabla; no se maneja ni modifica ningún campo `autenticado` desde aquí.
try {
    require_once __DIR__ . '/db.php';
} catch (Exception $e) {
    $_SESSION['error'] = 'Error de conexión con la base de datos.';
    header('Location: register.php'); exit;
}

// Recolectar POST
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : ''; 
$correo = isset($_POST['correo']) ? strtolower(trim($_POST['correo'])) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
$terms = isset($_POST['terms']) ? intval($_POST['terms']) : 0;

// Validaciones básicas
if ($nombre === '' || $apellido === '' || $correo === '' || $password === '' || $password_confirm === '') {
    $_SESSION['error'] = 'Por favor, rellena todos los campos.';
    header('Location: register.php'); exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Introduce un correo válido.';
    header('Location: register.php'); exit;
}
if (strlen($password) < 6) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
    header('Location: register.php'); exit;
}
if ($password !== $password_confirm) {
    $_SESSION['error'] = 'Las contraseñas no coinciden.';
    header('Location: register.php'); exit;
}
if ($terms !== 1) {
    $_SESSION['error'] = 'Debes aceptar los términos y condiciones.';
    header('Location: register.php'); exit;
}

// Comprobar si el correo ya existe
$tableBacktick = '`' . str_replace('`', '``', $DB_TABLE) . '`';
$emailCheckSql = "SELECT id FROM {$tableBacktick} WHERE correo = ? LIMIT 1";
$stmt = $mysqli->prepare($emailCheckSql);
if (!$stmt) {
    $_SESSION['error'] = 'Error interno (preparando consulta).';
    header('Location: register.php'); exit;
}
$stmt->bind_param('s', $correo);
if (!$stmt->execute()) {
    $stmt->close();
    $_SESSION['error'] = 'Error interno al comprobar correo.';
    header('Location: register.php'); exit;
}
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    $_SESSION['error'] = 'Ya existe una cuenta con ese correo.';
    header('Location: register.php'); exit;
}
$stmt->close();

$passHash = password_hash($password, PASSWORD_DEFAULT);
$passCol = '`' . str_replace('`', '``', $DB_PASSWORD_COLUMN) . '`';
$insertSql = "INSERT INTO {$tableBacktick} (nombre, apellido, correo, {$passCol}, money) VALUES (?, ?, ?, ?, 0)";
$stmt = $mysqli->prepare($insertSql);
if (!$stmt) {
    $_SESSION['error'] = 'Error interno (preparando inserción).';
    header('Location: register.php'); exit;
}
$stmt->bind_param('ssss', $nombre, $apellido, $correo, $passHash);
if (!$stmt->execute()) {
    $stmt->close();
    $_SESSION['error'] = 'Error al insertar el usuario.';
    header('Location: register.php'); exit;
}
$new_user_id = $mysqli->insert_id;
$stmt->close();

$_SESSION['success'] = 'Cuenta creada correctamente. Ya puedes optar por iniciar sesión.';
// Redirigimos al login y pasamos el correo para que el campo quede pre-rellenado
header('Location: index.php?correo=' . urlencode($correo)); exit;

?>
