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
security::checkType(security::ADMIN);

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

  <style>
  .categories-select {
    float: right;
    border-left: 1px solid #ccc;
    padding: 0 32px 16px 16px;
    user-select: none;
  }

  .categories-select .select-all {
    color: blue;
    text-decoration: underline;
    cursor: pointer;
  }

  @media (max-width: 500px) {
    .categories-select {
      float: none;
      border-left: none;
      border-top: 1px solid #ddd;
      border-bottom: 1px solid #ddd;
      padding: 0 0 16px 0;
      margin-bottom: 16px;
    }
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Exportar registro</h2>
          <p>Aquí puedes configurar cómo quieres exportar los datos del registro:</p>
          <form action="doexport.php" method="GET">
            <h5>Periodo</h5>
            <p>Del <input type="date" name="begins" max="<?=security::htmlsafe($yesterday)?>" required> al <input type="date" name="ends" max="<?=security::htmlsafe($yesterday)?>" required></p>

            <h5>Empresas</h5>
            <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
              <div id="companies" class="mdlext-selectfield__select mdl-custom-selectfield__select" tabindex="0">-</div>
              <ul class="mdl-menu mdl-menu--bottom mdl-js-menu mdl-custom-multiselect mdl-custom-multiselect-js" for="companies">
                <?php
                foreach (companies::getAll() as $id => $company) {
                  if ($id == calendars::TYPE_FESTIU) continue;
                  ?>
                  <li class="mdl-menu__item mdl-custom-multiselect__item">
                    <label class="mdl-checkbox mdl-js-checkbox" for="company-<?=(int)$id?>">
                      <input type="checkbox" id="company-<?=(int)$id?>" name="companies[]" value="<?=(int)$id?>" data-value="<?=(int)$id?>" class="mdl-checkbox__input">
                      <span class="mdl-checkbox__label"><?=security::htmlsafe($company)?></span>
                    </label>
                  </li>
                  <?php
                }
                ?>
              </ul>
              <label for="companies" class="mdlext-selectfield__label always-focused mdl-color-text--primary">Empresas</label>
            </div>

            <h5>Trabajadores</h5>
            <div class="categories-select">
              <h6>Seleccionar:</h6>
              <?php
              $categories = categories::getAllWithWorkers();
              foreach ($categories as $c) {
                if (!count($c["workers"])) continue;
                echo "<span class=\"select-all\" data-workers=\"".security::htmlsafe(implode(",", $c["workers"]))."\">".security::htmlsafe($c["name"])."</span><br>";
              }
              ?>
            </div>
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
                    <tr data-worker-id="<?=(int)$w["workerid"]?>" data-company-id="<?=(int)$w["companyid"]?>">
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

            <div style="clear: both;"></div>

            <h5>Formato</h5>
            <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
              <select name="format" id="format" class="mdlext-selectfield__select">
                <?php
                foreach (export::$formats as $i => $format) {
                  echo '<option value="'.(int)$i.'">'.security::htmlsafe($format).'</option>';
                }
                ?>
              </select>
              <label for="format" class="mdlext-selectfield__label">Formato</label>
            </div>

            <div id="pdf">
              <h5>Opciones para PDF</h5>
              <p>
                <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="ignoreempty">
                  <input type="checkbox" id="ignoreempty" name="ignoreempty" value="1" class="mdl-switch__input" checked>
                  <span class="mdl-switch__label">No incluir trabajadores que no tengan ningún registro ni incidencia</span>
                </label>
              </p>
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

          <?php visual::printDebug("categories::getAllWithWorkers()", $categories); ?>
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
