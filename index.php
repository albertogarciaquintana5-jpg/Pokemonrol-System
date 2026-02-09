<?php
include 'db.php';
session_start();
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error   = isset($_SESSION['error']) ? $_SESSION['error'] : null;
// Limpiar mensajes después de mostrarlos
unset($_SESSION['success'], $_SESSION['error']);
// Pre-fill correo if provided via GET (por ejemplo, tras registro)
$prefillCorreo = '';
if (isset($_GET['correo']) && filter_var($_GET['correo'], FILTER_VALIDATE_EMAIL)) {
  $prefillCorreo = trim($_GET['correo']);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Pokémon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="login-card p-4 text-center shadow-sm">

      <div class="login-pokeball mx-auto mb-3">
        <div class="center"></div>
      </div>
      <h3 class="title">Pokémon Borea Rol</h3>
      <p class="small-muted">Empieza tu aventura — inicia sesión con tu entrenador</p>

      <?php if ($success): ?>
        <div class="alert alert-success mt-3" role="alert"><?=htmlspecialchars($success);?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger mt-3" role="alert"><?=htmlspecialchars($error);?></div>
      <?php endif; ?>

      <form class="mt-3" action="login_process.php" method="post">
        <div class="mb-3 text-start">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" id="correo" name="correo" class="form-control" placeholder="tu@correo.com" value="<?= htmlspecialchars($prefillCorreo) ?>" required autofocus aria-label="Correo electrónico">
        </div>
        <div class="mb-3 text-start position-relative">
          <label for="password" class="form-label">Contraseña</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" placeholder="Tu contraseña secreta" required aria-label="Contraseña">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Mostrar contraseña">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Entrar</button>

        <div class="mt-3 small">¿No tienes cuenta? <a href="register.php">Regístrate</a></div>
      </form>

      <div class="mt-4 small text-muted footer-links">Por ALberto Garcia Quintana-Beta</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle password visibility
    const toggleBtn = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    toggleBtn.addEventListener('click', function(){
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.textContent = type === 'password' ? '👁️' : '🙈';
      this.setAttribute('aria-pressed', type === 'text' ? 'true' : 'false');
    });
  </script>
</body>
</html>
