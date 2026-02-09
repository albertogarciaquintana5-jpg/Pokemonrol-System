<?php
include 'db.php';
/*
  login_process.php
  Simulación de validación de usuario contra "MySQL".
  Debes adaptar la sección marcada a continuación con tu conexión real a la BD,
  usando PDO o mysqli y declaraciones preparadas.
*/
session_start();

// Usar la conexión y función de db.php
try {
  require_once __DIR__ . '/db.php';
} catch (Exception $e) {
  $_SESSION['error'] = 'Error al conectar con la base de datos. Contacte con ALberto.';
  header('Location: index.php'); exit;
}

// Si quieres comprobar, los hashes fueron generados con password_hash('pikachu', PASSWORD_DEFAULT) y password_hash('togepi', PASSWORD_DEFAULT)

$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($correo === '' || $password === '') {
    $_SESSION['error'] = 'Por favor, rellena todos los campos.';
    header('Location: index.php'); exit;
  }
  // Aquí usamos la función real de autenticación (usa PDO o mysqli en db.php)
  $result = auth_user_by_email($correo, $password);
  if ($result['success']) {
    $user = $result['user'];
    // Login correcto: crear sesión y redirigir
    $_SESSION['user'] = [
      'id' => $user['id'],
      'nombre' => $user['nombre'],
      'apellido' => $user['apellido'],
      'correo' => $user['correo']
    ];
    $_SESSION['success'] = "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!";
    // Redirigir a página "protegida" (puedes cambiarlo)
    header('Location: dashboard.php');
    exit;
  } else {
    // Login fallido: usamos el mensaje que devuelva la función
    $_SESSION['error'] = $result['error'] ?? 'Usuario o contraseña incorrecta.';
    header('Location: index.php');
    exit;
  }
}

/*
  --- Adaptación a MySQL real ---
  El ejemplo que sigue usa PDO y asume que tu tabla tiene al menos: id, nombre, apellido, correo, contraseña
  (ajusta los nombres de columnas a los de tu BD):

  $pdo = new PDO('mysql:host=localhost;dbname=tu_bd;charset=utf8mb4', 'user', 'pass', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $stmt = $pdo->prepare('SELECT id, nombre, apellido, correo, `contraseña` AS password_hash FROM usuarios WHERE correo = ? LIMIT 1');
  $stmt->execute([$correo]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($user && password_verify($password, $user['password_hash'])) { ... }

  También recuerda usar HTTPS, tokens de sesión seguros y políticas de CSP/Headers para producción.
*/

?>
