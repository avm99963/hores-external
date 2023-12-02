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

if (secondFactor::isAvailable()) $mdHeaderRowBefore = visual::backBtn("security.php");
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
          <h2>Cambiar contraseña</h2>
        	<form action="dochangepassword.php" method="POST">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="password" name="oldpassword" id="oldpassword" data-required>
              <label class="mdl-textfield__label" for="oldpassword">Contraseña actual</label>
            </div>
            <br>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="password" name="newpassword" id="newpassword" data-required>
              <label class="mdl-textfield__label" for="newpassword">Nueva contraseña</label>
            </div>
            <p class="mdl-color-text--grey-600"><?=security::htmlsafe(security::$passwordHelperText)?></p>

            <p><button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Cambiar contraseña</button></p>
          </form>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["wrong", "Ha ocurrido un error intentando cambiar la contraseña. Asegúrate de que has introducido correctamente la contraseña actual."]
  ]);
  ?>
</body>
</html>
