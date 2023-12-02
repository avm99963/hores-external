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

if (!security::checkParams("GET", [
  ["token", security::PARAM_NEMPTY]
])) {
  security::go("index.php?msg=unexpected");
}

$token = $_GET["token"];

$recovery = recovery::getUnusedRecovery($token);
if ($recovery === false) security::go("index.php?msg=recovery2failed");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/index.css">
  <script src="js/index.js"></script>
</head>
<?php visual::printBodyTag(); ?>
  <div class="login mdl-shadow--4dp">
    <h2>Restablecer contraseña</h2>
    <form action="dorecovery.php" method="POST" autocomplete="off" id="formulario">
      <input type="hidden" name="token" value="<?=security::htmlsafe($recovery["token"])?>">
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off" data-required>
        <label class="mdl-textfield__label" for="password">Contraseña</label>
      </div>
      <p class="mdl-color-text--grey-600"><?=security::htmlsafe(security::$passwordHelperText)?></p>

      <p><button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Restablecer</button></p>
		</form>
  </div>

  <?php
  visual::smartSnackbar([
    ["weakpassword", security::$passwordHelperText]
  ]);
  ?>
</body>
</html>
