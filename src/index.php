<?php
require_once("core.php");

if (security::isAllowed(security::ADMIN)) {
  security::go("home.php");
} elseif ($conf["enableWorkerUI"] && security::isAllowed(security::WORKER)) {
  security::go("workerhome.php");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/index.css">
  <script src="js/index.js"></script>
</head>
<?php visual::printBodyTag(); ?>
  <div class="login mdl-shadow--4dp">
    <h2><?=security::htmlsafe($conf["appName"])?></h2>
    <form action="signin.php" method="POST" autocomplete="off" id="formulario">
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="text" name="username" id="username" autocomplete="off" data-required>
        <label class="mdl-textfield__label" for="username">Nombre de usuario</label>
      </div>
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off" data-required>
        <label class="mdl-textfield__label" for="password">Contraseña</label>
      </div>
      <p><button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Iniciar sesión</button><?php if ($conf["enableRecovery"]) { ?> <button id="recoverybtn" class="mdl-button mdl-js-button mdl-js-ripple-effect">Recuperar contraseña</button><?php } ?></p>
		</form>
  </div>

  <?php
  if ($conf["enableRecovery"]) {
    ?>
    <dialog class="mdl-dialog" id="recovery">
      <form action="dostartrecovery.php" method="POST" enctype="multipart/form-data">
        <h4 class="mdl-dialog__title">Recuperar contraseña</h4>
        <div class="mdl-dialog__content">
          <p>Para recuperarla, introduce los siguientes datos:</p>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="email" name="email" id="email" autocomplete="off" data-required>
            <label class="mdl-textfield__label" for="email">Correo electrónico</label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="dni" id="dni" autocomplete="off" data-required>
            <label class="mdl-textfield__label" for="dni">DNI/NIF con letras mayúsculas</label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Recuperar</button>
          <button onclick="event.preventDefault(); document.querySelector('#recovery').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
        </div>
      </form>
    </dialog>
    <?php
  }

  visual::smartSnackbar([
    ["wrong", "Usuario y/o contraseña incorrecto."],
    ["empty", "Por favor, rellena todos los campos."],
    ["logout", "Has cerrado la sesión correctamente."],
    ["unsupported", "Todavía no puedes acceder como trabajador a la interfaz de trabajador."],
    ["unexpected", "Ha ocurrido un error inesperado."],
    ["recovery", "Si los datos que has proporcionado son correctos, se ha enviado un mensaje al correo electrónico indicado para proceder con la recuperación."],
    ["recovery2failed", "No se puede proceder con la recuperación, seguramente porque el enlace de recuperación ha expirado."],
    ["recoverycompleted", "Se ha cambiado la contraseña correctamente. Puedes iniciar sesión ahora con la nueva contraseña."],
    ["secondfactorwrongcode", "El código de verificación no es correcto."],
    ["signinthrottled", "No se ha podido verificar si el usuario y contraseña son correctos. Por favor, prueba de iniciar sesión de nuevo en unos instantes."]
  ]);
  ?>
</body>
</html>
