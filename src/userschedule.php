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
security::checkWorkerUIEnabled();

$isAdmin = security::isAllowed(security::ADMIN);

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go((security::isAdminView() ? "workers.php" : "workerschedule.php"));
}

$id = (int)$_GET["id"];

if (!$isAdmin && people::userData("id") != $id) {
  security::notFound();
}

$p = people::get($id);

if ($p === false) {
  security::go((security::isAdminView() ? "workers.php" : "workerschedule.php"));
}

$workers = workers::getPersonWorkers((int)$p["id"]);
$companies = companies::getAll();

if ($workers === false || $companies === false) {
  security::go((security::isAdminView() ? "workers.php?msg=unexpected" : "workerschedule.php?msg=unexpected"));
}

$mdHeaderRowBefore = visual::backBtn("workerschedule.php".(security::isAdminView() ? "?id=".$id : ""));
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <?php
  if (security::isAdminView()) {
    ?>
    <style>
    .addschedule {
      position: fixed;
      bottom: 16px;
      right: 16px;
      z-index: 1000;
    }
    </style>
    <?php
  }
  ?>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php
    visual::includeNav();

    if (security::isAdminView()) {
      ?>
      <button class="addschedule mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
      <?php
    }
    ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php $title = (security::isAdminView() ? "Horarios de &ldquo;".security::htmlsafe($p["name"])."&rdquo;" : "Todos los horarios"); ?>
          <h2><?=$title?></h2>

          <?php
          if (count($workers)) {
            foreach ($workers as $w) {
              $schedules = schedules::getAll((int)$w["id"], security::isAdminView());

              echo "<h4>".security::htmlsafe($companies[$w["company"]])."</h4>";

              if (count($schedules)) {
                foreach ($schedules as $s) {
                  ?>
                  <a href="schedule.php?id=<?=(int)$s["id"]?>" class="clicky-container">
                    <div class="clicky mdl-js-ripple-effect">
                      <div class="text">
                        <span class="title"><?=security::htmlsafe(date("d/m/Y", $s["begins"]))?> - <?=security::htmlsafe(date("d/m/Y", $s["ends"]))?></span><br>
                        <span class="description">Activo: <?=($s["active"] == 1 ? visual::YES : visual::NO)?></span>
                      </div>
                      <div class="mdl-ripple"></div>
                    </div>
                  </a>
                  <?php
                }
              } else {
                echo "<p>".(security::isAdminView() ? "Todavía no se ha definido ningún horario para este trabajador en esta empresa" : "Todavía no se ha definido ningún horario para esta empresa.")."</p>";
              }

              visual::printDebug("schedules::getAll(".(int)$w["id"].")", $schedules);
            }
          } else {
            echo "<p>".(security::isAdminView() ? "Antes de poder definir horarios para este trabajador deberías darlo de alta en alguna empresa." : "Todavía no se te ha definido ningún horario.")."</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  if (security::isAdminView()) {
    ?>
    <dialog class="mdl-dialog" id="addschedule">
      <form action="doaddschedule.php" method="POST" autocomplete="off">
        <h4 class="mdl-dialog__title">Crea un nuevo horario semanal</h4>
        <div class="mdl-dialog__content">
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="worker" id="worker" class="mdlext-selectfield__select" data-required>
              <?php
              foreach ($workers as $w) {
                echo '<option value="'.(int)$w["id"].'">'.security::htmlsafe($companies[$w["company"]]).'</option>';
              }
              ?>
            </select>
            <label for="worker" class="mdlext-selectfield__label">Empresa</label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off" data-required>
            <label class="mdl-textfield__label always-focused" for="begins">Fecha inicio de validez</label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" data-required>
            <label class="mdl-textfield__label always-focused" for="ends">Fecha fin de validez</label>
          </div>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Crear</button>
          <button onclick="event.preventDefault(); document.querySelector('#addschedule').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
        </div>
      </form>
    </dialog>
    <?php
  }
  ?>

  <?php
  visual::smartSnackbar([
    ["deleted", "Se ha eliminado el horario semanal correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["overlaps", "El horario que intentabas añadir se solapa con otro."],
    ["order", "La fecha de inicio debe ser anterior a la fecha de fin."]
  ]);
  ?>

  <?php if (security::isAdminView()) { ?><script src="js/userschedule.js"></script><?php } ?>
</body>
</html>
