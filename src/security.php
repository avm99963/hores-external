<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .highlighted {
    font-weight: 500;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Configuración de seguridad</h2>
          <a class="clicky-container" href="changepassword.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Cambiar contraseña</span><br>
                <span class="description">Haz clic para cambiar la contraseña de tu usuario.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>

          <hr>

          <h4>Verificación en dos pasos</h4>
          <p>La <span class="highlighted">verificación en dos pasos</span> es un sistema que <span class="highlighted">evita que terceros no autorizados puedan iniciar sesión</span> si te roban la contraseña.</p>
          <?php
          if (secondFactor::isEnabled()) {
            ?>
            <p>Cada vez que inicies sesión, tendrás que <span class="highlighted">introducir un código</span> que se genera automáticamente en tu móvil o <span class="highlighted">una llave de seguridad</span> para verificar que eres tú.</p>
            <p>La verificación en dos pasos <span class="highlighted">está activada</span>.</p>
            <p><a href="securitykeys.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"><i class="material-icons">vpn_key</i> <span>Llaves de seguridad</span></a> <a href="dynamic/disablesecondfactor.php?id=<?=(int)$_SESSION["id"]?>" data-dyndialog-href="dynamic/disablesecondfactor.php?id=<?=(int)$_SESSION["id"]?>" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Desactivar verificación en 2 pasos</a></p>
            <?php
          } else {
            ?>
            <p>Si la activas, a parte de introducir tu contraseña, tendrás que <span class="highlighted">introducir un código que se genera automáticamente en tu móvil</span> para verificar que eres tú quien intenta iniciar sesión.</p>
            <p>A parte, también puedes configurar como segundo factor una <span class="highlighted">llave de seguridad</span> física en vez de la verificación por código.</p>
            <p>Actualmente la verificación en dos pasos <span class="highlighted">no está activada</span>. Puedes configurarla haciendo clic en el siguiente botón:</p>
            <p><a href="dynamic/enablesecondfactor.php?id=<?=(int)$_SESSION["id"]?>" data-dyndialog-href="dynamic/enablesecondfactor.php?id=<?=(int)$_SESSION["id"]?>" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Empezar</a></p>
            <?php
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["wrongcode", "El código de verificación introducido no es correcto."],
    ["enabledsecondfactor", "Se ha activado la verificación en dos pasos correctamente."],
    ["wrongpassword", "La contraseña introducida no es correcta."],
    ["disabledsecondfactor", "Se ha desactivado la verificación en dos pasos correctamente."]
  ]);
  ?>

  <script src="lib/qrcodejs/qrcode.min.js"></script>
</body>
</html>
