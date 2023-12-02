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

if (!security::checkParams("POST", [
  ["type", security::PARAM_ISINT],
  ["workers", security::PARAM_ISARRAY],
  ["day", security::PARAM_ISDATE]
])) {
  security::go("incidents.php?msg=empty");
}

$type = (int)$_POST["type"];
$day = $_POST["day"];
$details = ((isset($_POST["details"]) && is_string($_POST["details"])) ? $_POST["details"] : "");

if (isset($_POST["allday"]) && $_POST["allday"] == 1) {
  $begins = 0;
  $ends = incidents::ENDOFDAY;
} else {
  if (!security::checkParams("POST", [
    ["begins", security::PARAM_ISTIME],
    ["ends", security::PARAM_ISTIME]
  ])) {
    security::go("incidents.php?msg=empty");
  }

  $begins = schedules::time2sec($_POST["begins"]);
  $ends = schedules::time2sec($_POST["ends"]);
}

$mdHeaderRowBefore = visual::backBtn("workers.php");
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
          <h2>Resultado de la creación de incidencias</h2>

          <?php
          foreach ($_POST["workers"] as $workerid) {
            $worker = workers::get($workerid);
            if ($worker === false) continue;

            $status = incidents::add($worker["id"], $type, $details, $day, $begins, $ends);

            $person = "&ldquo;".security::htmlsafe($worker["name"])."&rdquo; (".security::htmlsafe($worker["companyname"]).")";

            switch ($status) {
              case 0:
              echo "<p class='mdl-color-text--green'>Incidencia añadida correctamente a $person.";
              break;

              case 2:
              echo "<p class='mdl-color-text--orange'>La incidencia que se intentaba añadir se solapa con otra incidencia de $person, así que no se ha añadido a este trabajador.";
              break;

              case 3:
              echo "<p class='mdl-color-text--red'>No se ha podido añadir la incidencia a $person porque la fecha de inicio debe ser anterior a la de fin.";
              break;

              default:
              echo "<p class='mdl-color-text--red'>Ha ocurrido un error inesperado copiando la plantilla a $person.";
            }
            echo "</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
