<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

require_once("core.php");
security::checkType(security::WORKER);

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go("workerhome.php?msg=empty");
}

$isAdmin = security::isAllowed(security::ADMIN);

if (!$isAdmin && people::userData("id") != $_GET["id"]) {
  security::notFound();
}

$workers = workers::getPersonWorkers((int)$_GET["id"]);
if ($workers === false) security::go("workerhome.php?msg=unexpected");

$companies = companies::getAll();

$date = new DateTime();
$interval = new DateInterval("P1D");
$date->sub($interval);
$yesterday = date("Y-m-d", $date->getTimestamp());
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
          <?php helpView::renderHelpButton(help::PLACE_EXPORT_REGISTRY_PAGE, true); ?>
          <h2>Exportar registro</h2>
          <?php
          if (count($workers)) {
            ?>
            <form action="doexport.php" method="GET">
              <?php
              foreach ($workers as $w) {
                echo '<input type="hidden" name="workers[]" value="'.(int)$w["id"].'">';
              }
              ?>

              <h5>Periodo</h5>
              <p>Del <input type="date" name="begins" max="<?=security::htmlsafe($yesterday)?>" required> al <input type="date" name="ends" max="<?=security::htmlsafe($yesterday)?>" required></p>

              <h5>Formato</h5>
              <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
                <select name="format" id="format" class="mdlext-selectfield__select">
                  <?php
                  foreach (export::$formats as $i => $format) {
                    if (!in_array($i, export::$workerFormats)) continue;
                    echo '<option value="'.(int)$i.'">'.security::htmlsafe($format).'</option>';
                  }
                  ?>
                </select>
                <label for="format" class="mdlext-selectfield__label">Formato</label>
              </div>

              <div id="pdf">
                <h5>Opciones para PDF</h5>
                <p>
                  <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="labelinvalid">
                    <input type="checkbox" id="labelinvalid" name="labelinvalid" value="1" class="mdl-switch__input" checked>
                    <span class="mdl-switch__label">Marcar en rojo incidencias/registros no validados</span>
                  </label>
                </p>
                <p style="font-weight: bold;">
                  Mostrar registros/incidencias que estén:
                </p>
                <p>
                  <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="showvalidated">
                    <input type="checkbox" id="showvalidated" name="showvalidated" value="1" class="mdl-checkbox__input" checked>
                    <span class="mdl-checkbox__label">Validados</span>
                  </label>
                  <br>
                  <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="shownotvalidated">
                    <input type="checkbox" id="shownotvalidated" name="shownotvalidated" value="1" class="mdl-checkbox__input" checked>
                    <span class="mdl-checkbox__label">No validados</span>
                  </label>
                </p>
              </div>
              <br>
              <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Exportar</button>
            </form>
            <?php
          } else {
            echo "<p>No puedes exportar el registro porque todavía no se te ha asignado ninguna empresa.</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["inverted", "La fecha de inicio debe ser anterior a la de fin."],
    ["forecastingthefutureisimpossible", "La fecha de fin debe ser anterior al día de hoy."]
  ]);
  ?>

  <script src="js/export.js"></script>
</body>
</html>
