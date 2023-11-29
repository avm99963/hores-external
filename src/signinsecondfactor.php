<?php
require_once("core.php");

if (security::userType() !== security::UNKNOWN || !isset($_SESSION["firstfactorid"]) || !secondFactor::isEnabled($_SESSION["firstfactorid"])) {
  security::goHome();
}

secondFactor::checkAvailability();

$hasSecurityKeys = secondFactor::hasSecurityKeys($_SESSION["firstfactorid"]);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/index.css">
  <style>
  .login {
    max-width: 500px;
  }

  .mdl-tabs__tab-bar {
    height: auto;
  }

  .mdl-tabs__tab {
    overflow: visible;
    height: 100%;
    line-height: 1.5;
    padding-top: 14px;
    padding-bottom: 14px;
  }

  .mdl-tabs__panel {
    padding-top: 16px;
  }

  #content .mdl-spinner {
    margin: 16px auto;
  }

  #webauthn {
    text-align: center;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="login mdl-shadow--4dp">
    <h2>Verificación en dos pasos</h2>
    <div id="content">
      <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
        <div class="mdl-tabs__tab-bar">
          <a href="#totp" class="mdl-tabs__tab<?=($hasSecurityKeys ? "" : " is-active")?>">Código de verificación</a>
          <?php if ($hasSecurityKeys) { ?><a href="#webauthn" class="mdl-tabs__tab is-active">Llave de seguridad</a><?php } ?>
        </div>

        <div class="mdl-tabs__panel<?=($hasSecurityKeys ? "" : " is-active")?>" id="totp">
          <p>Introduce el código de verificación generado por tu aplicación para móviles.</p>

          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" name="code" id="code" autocomplete="off" inputmode="numeric" pattern="[0-9]{6}" data-required>
            <label class="mdl-textfield__label" for="code">Código de verificación</label>
          </div>
          <br>
          <button id="verify" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Verificar</button>
        </div>

        <?php
        if ($hasSecurityKeys) {
          ?>
          <div class="mdl-tabs__panel is-active" id="webauthn">
            <p>Cuando estés listo para autenticarte, pulsa el siguiente botón:</p>

            <button id="startwebauthn" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Usar llave de seguridad</button>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  </div>

  <div class="mdl-snackbar mdl-js-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button type="button" class="mdl-snackbar__action"></button>
  </div>

  <script src="js/common_webauthn.js"></script>
  <script src="js/secondfactor.js"></script>
</body>
</html>
