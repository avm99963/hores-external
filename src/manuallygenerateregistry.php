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
security::checkType(security::HYPERADMIN);

$companies = companies::getAll();

$advancedMode = (isset($_GET["advanced"]) && $_GET["advanced"] == "1");
if ($advancedMode) $conf["backgroundColor"] = "red-200";

$mdHeaderRowBefore = visual::backBtn("powertools.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
  .advanced-mode {
    border: dotted 3px red;
    padding: 13px 13px 29px 13px;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp<?=($advancedMode ? " advanced-mode" : "")?>">
          <div class="actions">
            <?php
            if ($advancedMode) {
              ?>
              <a href="manuallygenerateregistry.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Desactivar modo avanzado</a>
              <?php
            } else {
              ?>
              <a href="manuallygenerateregistry.php?advanced=1" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect">Activar modo avanzado</a>
              <?php
            }
            ?>
          </div>

          <h2>Generar registros manualmente</h2>
          <form action="domanuallygenerateregistry.php" method="POST">
            <?php
            if ($advancedMode) {
              ?>
              <input type="hidden" name="advanced" value="1">
              <p><span style="font-weight: bold; color: red;">ATENCIÓN:</span> al generar los registros en un intervalo de días, hay que hacer los siguientes pasos adicionales para asegurarse que estos se generan correctamente:</p>
              <ol>
                <li>Antes de hacer clic en el botón <b>Generar</b>, hay que asegurarse que <b>las fechas de inicio y fin sean correctas</b>. Si no, se podrían generar registros en muchos días no deseados, y el resultado puede ser muy costoso de revertir.</li>
                <li>Al acabarse de generar los registros, hay que ir al apartado <b>Logs</b> de la configuración y asegurarse que los logs correspondientes a los días generados <b>no tengan ningún icono de error al lado en el listado</b>. Si ha habido un error, cabe la posibilidad de que los registros no se hayan generado correctamente y hace falta comprobar si se han generado o no todos. (Para más información, léase <a href="https://avm99963.github.io/hores-external/administradores/registros/#ver-logs" target="_blank" rel="noopener noreferrer">este artículo de ayuda</a>)</li>
              </ol>
              <p>Teniendo en cuenta esto, selecciona los trabajadores y el periodo de días para el cual quieres generar los registros:</p>
              <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off" data-required>
                <label class="mdl-textfield__label always-focused" for="begins">Día inicio</label>
              </div>
              <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" data-required>
                <label class="mdl-textfield__label always-focused" for="ends">Día fin</label>
              </div>
              <?php
            } else {
              ?>
              <p>Selecciona los trabajadores y el día para el cual quieres generar los registros:</p>
              <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <input class="mdl-textfield__input" type="date" name="day" id="day" autocomplete="off" data-required>
                <label class="mdl-textfield__label always-focused" for="day">Día</label>
              </div>
              <?php
            }
            ?>

            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp">
                <thead>
                  <tr>
                    <?php
                    if ($conf["debug"]) {
                      ?>
                      <th class="extra">ID</th>
                      <?php
                    }
                    ?>
                    <th class="mdl-data-table__cell--non-numeric">Nombre</th>
                    <th class="mdl-data-table__cell--non-numeric">Empresa</th>
                    <th class="mdl-data-table__cell--non-numeric extra">Categoría</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $workers = people::getAll(false, true);
                  foreach ($workers as $w) {
                    ?>
                    <tr data-worker-id="<?=(int)$w["workerid"]?>">
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <td class="extra"><?=(int)$w["workerid"]?></td>
                        <?php
                      }
                      ?>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($w["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($companies[$w["companyid"]])?></td>
                      <td class="mdl-data-table__cell--non-numeric extra"><?=security::htmlsafe($w["category"])?></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <br>
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Generar</button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["done", "Se ha ejecutado la acción. Accede al apartado de logs para ver si se han generado los registros correctamente o no."],
    ["generatederr", "Ha ocurrido un error y no se ha podido guardar el log con la información de lo que ha hecho el programa."]
  ]);

  $logId = (int)($_GET["logId"] ?? 0);
  if ($logId > 0) {
    echo "<script>dynDialog.load('dynamic/log.php?id=".$logId."')</script>";
  }
  ?>
  <script src="js/invalidatebulkrecords.js"></script>
</body>
</html>
