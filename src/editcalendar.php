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

$mdHeaderRowBefore = visual::backBtn("calendars.php");

if (!security::checkParams("GET", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("calendars.php");
}

$id = (int)$_GET["id"];

$c = calendars::get($id);

if ($c === false) {
  security::go("calendars.php");
}

$details = json_decode($c["details"], true);

if (json_last_error() !== JSON_ERROR_NONE) {
  security::go("calendars.php?msg=unexpected");
}

$viewOnly = (isset($_GET["view"]) && $_GET["view"] == 1);
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
          <h2>Calendario de &ldquo;<?=security::htmlsafe($c["categoryname"])?>&rdquo;</h2>
          <?php
          $current = new DateTime();
          $current->setTimestamp($c["begins"]);
          $ends = new DateTime();
          $ends->setTimestamp($c["ends"]);
          ?>
          <form action="doeditcalendar.php" method="POST">
            <input type="hidden" name="id" value="<?=(int)$id?>">
            <?php
            calendarsView::renderCalendar($current, $ends, function ($timestamp, $id, $dow, $dom, $extra) {
              return ($extra[$timestamp] == $id);
            }, $viewOnly, $details);

            if (!$viewOnly)  {
              ?>
              <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Modificar</button>
              <?php
            }
            ?>
          </form>
        </div>
      </div>
    </main>
  </div>

  <script src="js/calendar.js"></script>

  <?php
  visual::smartSnackbar([
    ["inverted", "La fecha de inicio debe ser anterior a la fecha de fin."],
    ["overlap", "El calendario que intentabas añadir se solapa con uno ya existente."]
  ]);
  ?>
</body>
</html>
