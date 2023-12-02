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

$mdHeaderRowBefore = visual::backBtn("powertools.php");
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
          <h2>Enviar enlaces para generar contraseña</h2>
          <p>Selecciona las personas a las que quieres enviar un correo para que puedan establecer su contraseña:</p>
          <form action="dosendbulkpasswords.php" method="POST">
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp">
                <thead>
                  <tr>
                    <?php
                    if ($conf["debug"]) {
                      ?>
                      <th class="extra">ID</th>
                      <?php
                    }
                    ?>
                    <th class="mdl-data-table__cell--non-numeric">Nombre</th>
                    <th class="mdl-data-table__cell--non-numeric extra">Categoría</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $people = people::getAll();
                  foreach ($people as $p) {
                    ?>
                    <tr data-person-id="<?=(int)$p["id"]?>">
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <td class="extra"><?=(int)$p["id"]?></td>
                        <?php
                      }
                      ?>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($p["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric extra"><?=security::htmlsafe($p["category"])?></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <br>
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Enviar</button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>
  <script src="js/sendbulkpasswords.js"></script>
</body>
</html>
