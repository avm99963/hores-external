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

$id = $_GET["id"] ?? people::userData("id");
$id = (int)$id;

if (!$isAdmin && people::userData("id") != $id) {
  security::notFound();
}

$p = people::get($id);

if ($p === false) {
  security::go((security::isAdminView() ? "workers.php" : "workerhome.php"));
}

if (security::isAdminView()) $mdHeaderRowBefore = visual::backBtn("workers.php");

$plaintext = isset($_GET["plaintext"]) && $_GET["plaintext"] == "1";
$companies = companies::getAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/schedule.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div class="actions">
            <?php
            if (!security::isAdminView()) {
              ?>
              <a href="workercalendar.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"><i class="material-icons">today</i> Calendario laboral</a>
              <?php
            }
            ?>
            <a href="userschedule.php?id=<?=(int)$id?>" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"><i class="material-icons">timelapse</i> <?=(security::isAdminView() ? "Administrar los horarios" : "Todos los horarios")?></a>
          </div>

          <h2>Horario actual<?=(security::isAdminView() ? " de &ldquo;".security::htmlsafe($p["name"])."&rdquo;" : "")?></h2>

          <?php
          $schedules = schedules::getCurrent($id);

          if (!count($schedules)) {
            echo "<p>Actualmente no tienes asignado ningún horario.</p>";
          } else {
            foreach ($schedules as $i => $schedule) {
              $worker = workers::get($schedule["worker"]);

              if (security::isAdminView()) {
                ?>
                <a href="schedule.php?id=<?=(int)$schedule["id"]?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" style="float: right;"><i class="material-icons">edit</i><span class="mdl-ripple"></span></a>
                <?php
              }
              ?>
              <p>
                <b>Empresa:</b> <?=$companies[$worker["company"]]?><br>
                <b>Validez:</b> del <?=security::htmlsafe(date("d/m/Y", $schedule["begins"]))?> al <?=security::htmlsafe(date("d/m/Y", $schedule["ends"]))?>
              </p>
              <?php
              if ($plaintext) {
                $flag = false;
                foreach ($schedule["days"] as $typeday) {
                  schedulesView::renderPlainSchedule($typeday, false, null, null, $flag);
                }

                if (!$flag) {
                  echo "<p>Tu horario todavía no está configurado.</p>";
                }
              } else {
                foreach (calendars::$types as $tdid => $type) {
                  if ($tdid == calendars::TYPE_FESTIU) continue;

                  $tdisset = isset($schedule["days"][$tdid]);

                  echo "<h4>".security::htmlsafe(calendars::$types[$tdid])."</h4>";

                  if ($tdisset) {
                    schedulesView::renderSchedule($schedule["days"][$tdid], false, null, null);
                  } else {
                    echo "<p>Todavía no hay configurado ningún horario en días del tipo \"".security::htmlsafe(calendars::$types[$tdid])."\".</p>";
                  }
                }
              }

              if ($i + 1 != count($schedules)) echo "<hr>";
            }
          }
          ?>

          <?php visual::printDebug("schedules::getCurrent()", $schedules); ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>
</body>
</html>
