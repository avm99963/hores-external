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

$mdHeaderRowMore = '<div class="mdl-layout-spacer"></div>
<div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable
            mdl-textfield--floating-label mdl-textfield--align-right">
  <label class="mdl-button mdl-js-button mdl-button--icon"
         for="usuario">
    <i class="material-icons">search</i>
  </label>
  <div class="mdl-textfield__expandable-holder">
    <input class="mdl-textfield__input" type="text" name="usuario"
           id="usuario">
  </div>
</div>';

listings::buildSelect("workers.php");

$companies = companies::getAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .filter {
    position:fixed;
    bottom: 25px;
    right: 25px;
    z-index: 1000;
  }

  @media (max-width: 655px) {
    .extra {
      display: none;
    }
  }

  table.has-actions-above {
    border-top: 0!important;
  }

  /* Hide datable's search box */
  .dataTables_wrapper .mdl-grid:first-child {
    display: none;
  }
  .dt-table {
    padding: 0!important;
  }
  .dt-table .mdl-cell {
    margin: 0!important;
  }
  #usuario {
    position: relative;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>

    <button class="filter mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">filter_list</i></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div class="actions">
            <a href="scheduletemplates.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"><i class="material-icons">timelapse</i> Administrar plantillas</a>
          </div>

          <h2>Trabajadores</h2>
          <div class="left-actions">
            <button id="copytemplate" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons icon">add_alarm</i></button>
            <button id="addincidentbulk" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons icon">note_add</i></button>
            <button id="addrecurringincident" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons icon">repeat</i></button>
          </div>
          <?php
          visual::addTooltip("copytemplate", "Copiar plantilla de horario a los trabajadores seleccionados");
          visual::addTooltip("addincidentbulk", "Añadir incidencia a los trabajadores seleccionados");
          visual::addTooltip("addrecurringincident", "Añadir incidencia recurrente al trabajador seleccionado");
          ?>
          <div class="overflow-wrapper overflow-wrapper--for-table">
            <table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp datatable has-actions-above">
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
                  <th class="mdl-data-table__cell--non-numeric"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $workers = people::getAll($select, true);
                foreach ($workers as $w) {
                  $schedulesStatusDetailed = schedules::getWorkerScheduleStatus($w["workerid"]);
                  $schedulesStatus = $schedulesStatusDetailed["status"];
                  if ($selectedSchedulesStatus !== false && !in_array($schedulesStatus, $selectedSchedulesStatus)) continue;
                  ?>
                  <tr data-worker-id="<?=(int)$w["workerid"]?>"<?=($w["hidden"] == 1 ? "class=\"mdl-color-text--grey-700\"" : "")?>>
                    <?php
                    if ($conf["debug"]) {
                      ?>
                      <td class="extra"><?=(int)$w["workerid"]?></td>
                      <?php
                    }
                    ?>
                    <td class="mdl-data-table__cell--non-numeric"><?=($w["hidden"] == 1 ? "<s>" : "").security::htmlsafe($w["name"]).($w["hidden"] == 1 ? "</s>" : "")?></td>
                    <td class="mdl-data-table__cell--non-numeric"><?=($w["hidden"] == 1 ? "<s>" : "").security::htmlsafe($companies[$w["companyid"]]).($w["hidden"] == 1 ? "</s>" : "")?></td>
                    <td class="mdl-data-table__cell--non-numeric extra"><?=($w["hidden"] == 1 ? "<s>" : "").security::htmlsafe($w["category"]).($w["hidden"] == 1 ? "</s>" : "")?></td>
                    <td class='mdl-data-table__cell--non-numeric'>
                      <a href='<?=($schedulesStatus == schedules::STATUS_NO_ACTIVE_SCHEDULE ? "userschedule" : "workerschedule")?>.php?id=<?=(int)$w['id']?>' title='Ver y gestionar los horarios del trabajador' class='mdl-color-text--<?=security::htmlsafe((schedules::$workerScheduleStatusColors[$schedulesStatus] ?? "black"))?>'><i class='material-icons icon-no-black'>timelapse</i></a>
                      <a href='userincidents.php?id=<?=(int)$w['id']?>' title='Ver y gestionar las incidencias del trabajador'><i class='material-icons icon'>assignment_late</i></a>
                      <a href='userregistry.php?id=<?=(int)$w['id']?>' title='Ver y gestionar los registros del trabajador'><i class='material-icons icon'>list</i></a>
                      <a href='dynamic/workhistory.php?id=<?=(int)$w['workerid']?>' data-dyndialog-href='dynamic/workhistory.php?id=<?=(int)$w['workerid']?>' title='Acceder al historial de altas y bajas'><i class='material-icons icon'>history</i></a>
                    </td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
            </table>
          </div>

          <?php visual::printDebug("people::getAll(\$select, true)", $workers); ?>
          <?php visual::printDebug("\$select", $select); ?>
        </div>
      </div>
    </main>
  </div>

  <?php listings::renderFilterDialog("workers.php", $select); ?>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["copied", "Plantilla copiada correctamente."],
    ["empty", "Faltaban datos por rellenar en el formulario o ha ocurrido un error inesperado."]
  ]);
  ?>

  <script src="js/workers.js"></script>
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
  <script src="lib/datatables/dataTables.material.min.js"></script>

  <?php
  if (isset($_GET["openWorkerHistory"])) {
    ?>
    <script>
    window.addEventListener("load", _ => {
      dynDialog.load("dynamic/workhistory.php?id=<?=(int)$_GET["openWorkerHistory"]?>");
    });
    </script>
    <?php
  }
  ?>
</body>
</html>
