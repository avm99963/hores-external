<?php
require_once("core.php");
security::checkType(security::WORKER);

$id = people::userData("id");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/incidents.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php helpView::renderHelpButton(help::PLACE_VALIDATION_PAGE, true); ?>
          <h2>Validaciones</h2>
          <p>Selecciona todas las incidencias y registros de horario a los cuales quieres dar validez.</p>
          <?php
          validationsView::renderPendingValidations($id);
          ?>
        </div>
      </div>
    </main>
  </div>

  <div class="mdl-snackbar mdl-js-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button type="button" class="mdl-snackbar__action"></button>
  </div>

  <?php
  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["success", "Se ha realizado la validación correctamente."],
    ["partialsuccess", "Ha ocurrido un error validando alguno de los elementos. Inténtelo de nuevo en unos segundos."]
  ], 10000, false);
  ?>

  <script src="js/incidentsgeneric.js"></script>
  <script src="js/validations.js"></script>
</body>
</html>
