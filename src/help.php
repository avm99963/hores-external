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

$mdHeaderRowBefore = visual::backBtn("settings.php");
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
          <h2>Recursos de ayuda</h2>
          <div class="overflow-wrapper overflow-wrapper--for-table">
            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
              <thead>
                <tr>
                  <th class="mdl-data-table__cell--non-numeric">Sitio</th>
                  <th class="mdl-data-table__cell--non-numeric">URL</th>
                  <th class="mdl-data-table__cell--non-numeric"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach (help::$places as $p) {
                  $h = help::get($p);
                  $isset = ($h !== false);
                  ?>
                  <tr<?=(!$isset ? " class='mdl-color-text--grey-600'" : "")?>>
                    <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(help::$placesName[$p])?></td>
                    <td class="mdl-data-table__cell--non-numeric"><?=($h === false ? "-" : $h)?></td>
                    <td class='mdl-data-table__cell--non-numeric'><a href='dynamic/sethelpresource.php?place=<?=security::htmlsafe($p)?>' data-dyndialog-href='dynamic/sethelpresource.php?place=<?=security::htmlsafe($p)?>' title='Configurar enlace de ayuda'><i class='material-icons icon'>build</i></a></td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["success", "Se ha configurado el enlace correctamente."],
    ["invalidurl", "La URL proporcionada está malformada. Por favor, introduce una URL correcta."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>
</body>
</html>
