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

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go("scheduletemplates.php");
}

$id = (int)$_GET["id"];

$template = schedules::getTemplate($id);

if ($template === false) {
  security::go("scheduletemplates.php");
}

$plaintext = isset($_GET["plaintext"]) && $_GET["plaintext"] == "1";

$mdHeaderRowBefore = visual::backBtn("scheduletemplates.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/schedule.css">

  <style>
  .addday {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <button class="addday mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div class="actions">
            <button id="menu" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons">more_vert</i></button>
          </div>

          <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="menu">
            <a data-dyndialog-href="dynamic/editscheduletemplate.php?id=<?=(int)$template["id"]?>" href="dynamic/editscheduletemplate.php?id=<?=(int)$template["id"]?>"><li class="mdl-menu__item">Editar detalles</li></a>
            <a data-dyndialog-href="dynamic/deletescheduletemplate.php?id=<?=(int)$template["id"]?>" href="dynamic/deletescheduletemplate.php?id=<?=(int)$template["id"]?>"><li class="mdl-menu__item">Eliminar</li></a>
            <a href="scheduletemplate.php?id=<?=(int)$template["id"]?>&plaintext=<?=($plaintext ? "0" : "1")?>"><li class="mdl-menu__item"><?=($plaintext ? "Versión enriquecida" : "Versión en texto plano")?></li></a>
          </ul>

          <h2>Plantilla &ldquo;<?=security::htmlsafe($template["name"])?>&rdquo;</h2>

          <p><b>Validez:</b> del <?=security::htmlsafe(date("d/m/Y", $template["begins"]))?> al <?=security::htmlsafe(date("d/m/Y", $template["ends"]))?></p>

          <?php
          if ($plaintext) {
            echo "<hr>";

            $flag = false;
            foreach ($template["days"] as $typeday) {
              schedulesView::renderPlainSchedule($typeday, true, function($day) {
                return "dynamic/edittemplateday.php?id=".(int)$day["id"];
              }, function ($day) {
                return "dynamic/deletetemplateday.php?id=".(int)$day["id"];
              }, $flag);
            }

            if (!$flag) {
              echo "<p>Esta plantilla todavía no está configurada.</p><p>Haz clic en el botón de la parte inferior derecha para empezar a rellenar los horarios de la plantilla.</p>";
            }
          } else {
            foreach (calendars::$types as $tdid => $type) {
              if ($tdid == calendars::TYPE_FESTIU) continue;

              $tdisset = isset($template["days"][$tdid]);

              echo "<h4>".security::htmlsafe(calendars::$types[$tdid])."</h4>";

              if ($tdisset) {
                schedulesView::renderSchedule($template["days"][$tdid], true, function($day) {
                  return "dynamic/edittemplateday.php?id=".(int)$day["id"];
                }, function ($day) {
                  return "dynamic/deletetemplateday.php?id=".(int)$day["id"];
                });
              } else {
                echo "<p>Todavía no hay configurado ningún horario en días del tipo \"".security::htmlsafe(calendars::$types[$tdid])."\".</p>";
              }
            }
          }
          ?>

          <?php visual::printDebug("schedules::getTemplate(".(int)$template["id"].")", $template); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addday">
    <form action="doadddayscheduletemplate.php" method="POST" autocomplete="off">
      <input type="hidden" name="id" value="<?=(int)$template["id"]?>">
      <h4 class="mdl-dialog__title">Añade un nuevo horario</h4>
      <div class="mdl-dialog__content">
        <h5>Día</h5>
        <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
          <div id="dayMenu" class="mdlext-selectfield__select mdl-custom-selectfield__select" tabindex="0">-</div>
          <ul class="mdl-menu mdl-menu--bottom mdl-js-menu mdl-custom-multiselect mdl-custom-multiselect-js" for="dayMenu">
            <?php
            foreach (calendars::$days as $id => $day) {
              ?>
              <li class="mdl-menu__item mdl-custom-multiselect__item">
                <label class="mdl-checkbox mdl-js-checkbox" for="day-<?=(int)$id?>">
                  <input type="checkbox" id="day-<?=(int)$id?>" name="day[]" value="<?=(int)$id?>" data-value="<?=(int)$id?>" class="mdl-checkbox__input">
                  <span class="mdl-checkbox__label"><?=security::htmlsafe($day)?></span>
                </label>
              </li>
              <?php
            }
            ?>
          </ul>
          <label for="day" class="mdlext-selectfield__label always-focused mdl-color-text--primary">Día de la semana</label>
        </div>
        <br>
        <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
          <div id="dayType" class="mdlext-selectfield__select mdl-custom-selectfield__select" tabindex="0">-</div>
          <ul class="mdl-menu mdl-menu--bottom mdl-js-menu mdl-custom-multiselect mdl-custom-multiselect-js" for="dayType">
            <?php
            foreach (calendars::$types as $id => $type) {
              if ($id == calendars::TYPE_FESTIU) continue;
              ?>
              <li class="mdl-menu__item mdl-custom-multiselect__item">
                <label class="mdl-checkbox mdl-js-checkbox" for="type-<?=(int)$id?>">
                  <input type="checkbox" id="type-<?=(int)$id?>" name="type[]" value="<?=(int)$id?>" data-value="<?=(int)$id?>" class="mdl-checkbox__input">
                  <span class="mdl-checkbox__label"><?=security::htmlsafe($type)?></span>
                </label>
              </li>
              <?php
            }
            ?>
          </ul>
          <label for="day" class="mdlext-selectfield__label always-focused mdl-color-text--primary">Tipo de día</label>
        </div>

        <h5>Jornada laboral</h5>
        <p>De <input type="time" name="beginswork" required> a <input type="time" name="endswork" required></p>

        <h5>Desayuno</h5>
        <p>De <input type="time" name="beginsbreakfast"> a <input type="time" name="endsbreakfast"></p>

        <h5>Comida</h5>
        <p>De <input type="time" name="beginslunch"> a <input type="time" name="endslunch"></p>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary">Crear</button>
        <button onclick="event.preventDefault(); document.querySelector('#addday').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::smartSnackbar([
    ["added", "Se ha añadido el horario correctamente."],
    ["modified", "Se ha modificado el horario correctamente."],
    ["deleted", "Se ha eliminado el horario correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["errorcheck1", "La hora de inicio debe ser anterior a la hora de fin."],
    ["errorcheck2", "El desayuno y comida deben estar dentro del horario de trabajo."],
    ["errorcheck3", "El desayuno y comida no se pueden solapar."],
    ["errorcheck4", "El horario de trabajo no puede ser nulo."],
    ["existing", "Algunos horarios que has intentado introducir ya existían. Estos no se han añadido."],
    ["order", "La fecha de inicio debe ser anterior a la fecha de fin."]
  ]);
  ?>

  <script src="js/schedule.js"></script>
</body>
</html>
