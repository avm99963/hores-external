<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

$isAdmin = security::isAllowed(security::ADMIN);

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go((security::isAdminView() ? "workers.php" : "userschedule.php?id=".(int)$_SESSION["id"]));
}

$id = (int)$_GET["id"];

$schedule = schedules::get($id);

if ($schedule === false) {
  security::go((security::isAdminView() ? "workers.php" : "userschedule.php?id=".(int)$_SESSION["id"]));
}

$worker = workers::get($schedule["worker"]);

if ($worker === false) {
  security::go((security::isAdminView() ? "workers.php" : "userschedule.php?id=".(int)$_SESSION["id"])."?msg=unexpected");
}

if (!$isAdmin && people::userData("id") != $worker["person"]) {
  security::notFound();
}

$plaintext = isset($_GET["plaintext"]) && $_GET["plaintext"] == "1";

$mdHeaderRowBefore = visual::backBtn((security::isAdminView() ? "userschedule.php?id=".$worker["person"] : "userschedule.php?id=".(int)$_SESSION["id"]));
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/schedule.css">

  <?php
  if (security::isAdminView()) {
    ?>
    <style>
    .addday {
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
      <button class="addday mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
      <?php
    }
    ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <div class="actions">
            <?php
            if (security::isAdminView()) {
              ?>
              <form action="doactiveschedule.php" method="POST" style="display: inline-block;">
                <input type="hidden" name="id" value="<?=(int)$schedule["id"]?>">
                <input type="hidden" name="value" value="<?=((int)$schedule["active"] + 1)%2?>">
                <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect" ><?=($schedule["active"] == 0 ? "Activar" : "Desactivar")?></button>
              </form>
              <?php
            }
            ?>
            <button id="menu" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons">more_vert</i></button>
          </div>

          <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="menu">
            <?php
            if (security::isAdminView()) {
              ?>
              <a data-dyndialog-href="dynamic/editschedule.php?id=<?=(int)$schedule["id"]?>" href="dynamic/editschedule.php?id=<?=(int)$schedule["id"]?>"><li class="mdl-menu__item">Editar detalles</li></a>
              <a data-dyndialog-href="dynamic/deleteschedule.php?id=<?=(int)$schedule["id"]?>" href="dynamic/deleteschedule.php?id=<?=(int)$schedule["id"]?>"><li class="mdl-menu__item">Eliminar</li></a>
              <?php
            }
            ?>
            <a href="schedule.php?id=<?=(int)$schedule["id"]?>&plaintext=<?=($plaintext ? "0" : "1")?>"><li class="mdl-menu__item"><?=($plaintext ? "Versión enriquecida" : "Versión en texto plano")?></li></a>
          </ul>

          <h2>Horario semanal</h2>

          <p>
            <b>Trabajador:</b> <?=security::htmlsafe($worker["name"])?> (<?=security::htmlsafe($worker["companyname"])?>)<br>
            <b>Validez:</b> del <?=security::htmlsafe(date("d/m/Y", $schedule["begins"]))?> al <?=security::htmlsafe(date("d/m/Y", $schedule["ends"]))?><br>
            <b>Activo:</b> <?=($schedule["active"] == 1 ? visual::YES : visual::NO)?>
          </p>

          <?php
          if ($plaintext) {
            echo "<hr>";

            $flag = false;
            foreach ($schedule["days"] as $typeday) {
              if (security::isAdminView()) {
                schedulesView::renderPlainSchedule($typeday, true, function($day) {
                  return "dynamic/editday.php?id=".(int)$day["id"];
                }, function ($day) {
                  return "dynamic/deleteday.php?id=".(int)$day["id"];
                }, $flag);
              } else {
                schedulesView::renderPlainSchedule($typeday, false, null, null, $flag);
              }
            }

            if (!$flag) {
              echo "<p>Este horario todavía no está configurado.</p>".(security::isAdminView() ? "<p>Haz clic en el botón de la parte inferior derecha para empezar a rellenar los horarios diarios.</p>" : "");
            }
          } else {
            foreach (calendars::$types as $tdid => $type) {
              if ($tdid == calendars::TYPE_FESTIU) continue;

              $tdisset = isset($schedule["days"][$tdid]);

              echo "<h4>".security::htmlsafe(calendars::$types[$tdid])."</h4>";

              if ($tdisset) {
                if (security::isAdminView()) {
                  schedulesView::renderSchedule($schedule["days"][$tdid], true, function($day) {
                    return "dynamic/editday.php?id=".(int)$day["id"];
                  }, function ($day) {
                    return "dynamic/deleteday.php?id=".(int)$day["id"];
                  });
                } else {
                  schedulesView::renderSchedule($schedule["days"][$tdid], false, null, null);
                }
              } else {
                echo "<p>Todavía no hay configurado ningún horario en días del tipo \"".security::htmlsafe(calendars::$types[$tdid])."\".</p>";
              }
            }
          }
          ?>

          <?php visual::printDebug("schedules::get(".(int)$schedule["id"].")", $schedule); ?>
        </div>
      </div>
    </main>
  </div>

  <?php
    if (security::isAdminView()) {
      ?>
    <dialog class="mdl-dialog" id="addday">
      <form action="doadddayschedule.php" method="POST" autocomplete="off">
        <input type="hidden" name="id" value="<?=(int)$schedule["id"]?>">
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

    <script src="js/schedule.js"></script>
    <?php
  }

  visual::smartSnackbar([
    ["added", "Se ha añadido el horario correctamente."],
    ["modified", "Se ha modificado el horario correctamente."],
    ["deleted", "Se ha eliminado el horario diario correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["errorcheck1", "La hora de inicio debe ser anterior a la hora de fin."],
    ["errorcheck2", "El desayuno y comida deben estar dentro del horario de trabajo."],
    ["errorcheck3", "El desayuno y comida no se pueden solapar."],
    ["errorcheck4", "El horario de trabajo no puede ser nulo."],
    ["existing", "Algunos horarios que has intentado introducir ya existían. Estos no se han añadido."],
    ["activeswitched0", "Horario desactivado correctamente."],
    ["activeswitched1", "Horario activado correctamente."],
    ["order", "La fecha de inicio debe ser anterior a la fecha de fin."]
  ]);
  ?>
</body>
</html>
