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

if (!isset($_GET["id"])) {
  security::go("calendars.php");
}

$category = $_GET["id"];

$categoryd = categories::get($category);

if ($categoryd === false && $category != -1) {
  security::go("calendars.php");
}

if ($category == -1) {
  $categoryd = array("name" => "Calendario por defecto");
}

// These checks are just to know whether the user filled in the form or not.
// Thus, I added true as the third parameter to not display debug information when they fail.
$newCalendar = security::checkParams("GET", [
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
], true);

$importCalendar = security::checkParams("POST", [
  ["import", security::PARAM_NEMPTY]
], true);

$calendarEditor = ($newCalendar || $importCalendar);

if ($importCalendar) {
  $calendar = json_decode($_POST["import"], true);

  if (json_last_error() !== JSON_ERROR_NONE || !isset($calendar["begins"]) || !isset($calendar["ends"]) || !isset($calendar["calendar"])) {
    security::go("addcalendar.php?id=".(int)$category."&msg=jsoninvalid");
  }
}

$mdHeaderRowBefore = visual::backBtn(($calendarEditor ? "addcalendar.php?id=".(int)$category : "calendars.php"));
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/calendar.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Añadir calendario a &ldquo;<?=security::htmlsafe($categoryd["name"])?>&rdquo;</h2>
          <?php
          if ($calendarEditor) {
            if ($newCalendar) {
              $current = new DateTime($_GET["begins"]);
              $ends = new DateTime($_GET["ends"]);
            } else {
              $current = new DateTime();
              $current->setTimestamp((int)$calendar["begins"]);
              $ends = new DateTime();
              $ends->setTimestamp((int)$calendar["ends"]);
            }

            if ($current->diff($ends)->invert === 1) {
              security::go("addcalendar.php?id=".(int)$category."&msg=inverted");
            }

            if (calendars::checkOverlap($category, $current->getTimestamp(), $ends->getTimestamp())) {
              security::go("addcalendar.php?id=".(int)$category."&msg=overlap");
            }

            if ($importCalendar) {
              echo "<p>Este es el calendario que has importado. Ahora puedes hacer las modificaciones que creas oportunas y añadirlo.</p>";
            }
            ?>
            <form action="doaddcalendar.php" method="POST">
              <input type="hidden" name="id" value="<?=(int)$category?>">
              <input type="hidden" name="begins" value="<?=security::htmlsafe(($newCalendar ? $_GET["begins"] : $current->format("Y-m-d")))?>">
              <input type="hidden" name="ends" value="<?=security::htmlsafe(($newCalendar ? $_GET["ends"] : $ends->format("Y-m-d")))?>">
              <?php
              calendarsView::renderCalendar($current, $ends, ($newCalendar ? function ($timestamp, $id, $dow, $dom, $extra) {
                return (($dow >= 6 && $id == calendars::TYPE_FESTIU) || $dow < 6 && $id == calendars::TYPE_LECTIU);
              } : function ($timestamp, $id, $dow, $dom, $extra) {
                return ($extra[$timestamp] == $id);
              }), false, ($newCalendar ? false : $calendar["calendar"]));
              ?>
              <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Añadir</button>
            </form>
            <?php
          } else {
            ?>
            <p>Introduce las fechas de inicio y fin del calendario que quieres configurar:</p>
            <form action="addcalendar.php" method="GET">
              <input type="hidden" name="id" value="<?=(int)$category?>">
              <p><label for="begins">Fecha inicio:</label> <input type="date" id="begins" name="begins" required> <label for="ends">Fecha fin:</label> <input type="date" id="ends" name="ends" required></p>
              <p><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Empezar a configurar el calendario</button></p>
            </form>
            <hr>
            <p>Alternativamente, puedes importar un calendario para usarlo como plantilla y editarlo antes de crearlo:</p>
            <form action="addcalendar.php?id=<?=(int)$category?>" method="POST">
              <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                <textarea class="mdl-textfield__input" name="import" id="import" rows="3" data-required></textarea>
                <label class="mdl-textfield__label" for="import">Código JSON del calendario original</label>
              </div>
              <p><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Importar calendario</button></p>
            </form>
            <?php
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <script src="js/calendar.js"></script>

  <?php
  visual::smartSnackbar([
    ["inverted", "La fecha de inicio debe ser anterior a la fecha de fin."],
    ["overlap", "El calendario que intentabas añadir se solapa con uno ya existente."],
    ["jsoninvalid", "El código JSON del calendario que estás importando es incorrecto."]
  ]);
  ?>
</body>
</html>
