<?php
include 'db.php';
session_start();
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error   = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro Pokémon Rol</title>
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
      <h3 class="title">Crear una cuenta</h3>
      <p class="small-muted">Únete a la aventura — registra tu cuenta de entrenador</p>

      <?php if ($success): ?>
        <div class="alert alert-success mt-3" role="alert"><?=htmlspecialchars($success);?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger mt-3" role="alert"><?=htmlspecialchars($error);?></div>
      <?php endif; ?>

      <form class="mt-3" action="register_process.php" method="post" novalidate>
        <div class="row g-2">
          <div class="col-md-6">
            <label for="nombre" class="form-label text-start">Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ash" required>
          </div>
          <div class="col-md-6">
            <label for="apellido" class="form-label text-start">Apellido</label>
            <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ketchum" required>
          </div>
        </div>
        <div class="mb-3 text-start mt-2">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" id="correo" name="correo" class="form-control" placeholder="tu@correo.com" required>
        </div>
        <div class="mb-3 text-start position-relative">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Tu contraseña" minlength="6" required>
        </div>
        <div class="mb-3 text-start position-relative">
          <label for="password_confirm" class="form-label">Confirmar contraseña</label>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Repite la contraseña" minlength="6" required>
        </div>

        <div class="form-check text-start mb-3">
          <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" required>
          <label class="form-check-label" for="terms">Acepto los términos y condiciones</label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Crear cuenta</button>

        <div class="mt-3 small">¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a></div>
      </form>

      <div class="mt-4 small text-muted footer-links">Por ALberto Garcia Quintana-Beta</div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
