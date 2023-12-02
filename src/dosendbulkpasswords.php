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

if (!security::checkParams("POST", [
  ["people", security::PARAM_ISARRAY]
])) {
  security::go("sendbulkpasswords.php?msg=empty");
}
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
          <h2>Resultado del envío de enlaces</h2>

          <?php
          foreach ($_POST["people"] as $id) {
            $person = people::get($id);
            if ($person === false) continue;

            $status = recovery::recover($person["id"], recovery::EMAIL_TYPE_WELCOME);

            $personText = "&ldquo;".security::htmlsafe($person["name"])."&rdquo;";

            if ($status) {
              echo "<p class='mdl-color-text--green'>Enlace enviado correctamente a $personText.";
            } elseif ($status === recovery::EMAIL_NOT_SET) {
              echo "<p class='mdl-color-text--orange'>No se ha podido enviar el correo a $personText porque no tiene asociada ninguna dirección de correo electrónico.";
            } else {
              echo "<p class='mdl-color-text--red'>Ha ocurrido un error generando el enlace o enviando el correo a $personText.";
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
