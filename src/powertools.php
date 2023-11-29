<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

$mdHeaderRowBefore = visual::backBtn("settings.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Herramientas avanzadas</h2>
          <a class="clicky-container" href="backupdb.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Exportar base de datos</span><br>
                <span class="description">Genera un archivo con sentencias SQL para hacer una copia de seguridad de la base de datos.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="sendbulkpasswords.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Enviar enlaces para generar contraseña</span><br>
                <span class="description">Envía en masa a los trabajadores que desees un enlace vía correo electrónico para que establezcan una contraseña y puedan entrar en el aplicativo.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="invalidatebulkrecords.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Invalidar registros en masa</span><br>
                <span class="description">Invalida los registros de ciertos trabajadores en un periodo de tiempo concreto.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="manuallygenerateregistry.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Generar registros manualmente</span><br>
                <span class="description">Ejecuta el programa que genera registros a partir de los horarios configurados en un día concreto y para un subconjunto de trabajadores.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="pendingvalidations.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Validaciones pendientes</span><br>
                <span class="description">Ver un listado de personas que muestra el número de validaciones pendientes que tiene cada una.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
