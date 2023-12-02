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

$mdHeaderRowBefore = visual::backBtn("settings.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .add-calendar, .category {
    vertical-align: middle;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Calendarios</h2>
          <?php
          $calendars_response = calendars::getAll();

          foreach ($calendars_response as $ccs) {
            ?>
            <h4><span class="category"><?=security::htmlsafe($ccs["category"])?></span> <a href="addcalendar.php?id=<?=(int)$ccs["categoryid"]?>" class="mdl-button mdl-js-button mdl-button--icon mdl-button--accent add-calendar" id="cat<?=(int)$ccs["categoryid"]?>"><i class="material-icons">add</i></a></h4>
            <?php visual::addTooltip("cat".(int)$ccs["categoryid"], "Añadir un calendario a esta categoría"); ?>
            <?php
            if (count($ccs["calendars"])) {
              ?>
              <div class="overflow-wrapper overflow-wrapper--for-table">
                <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp datatable">
                  <thead>
                    <tr>
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <th class="extra">ID</th>
                        <?php
                      }
                      ?>
                      <th class="mdl-data-table__cell--non-numeric">Fecha inicio</th>
                      <th class="mdl-data-table__cell--non-numeric">Fecha fin</th>
                      <th class="mdl-data-table__cell--non-numeric"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($ccs["calendars"] as $c) {
                      ?>
                      <tr>
                        <?php
                        if ($conf["debug"]) {
                          ?>
                          <td class="extra"><?=(int)$c["id"]?></td>
                          <?php
                        }
                        ?>
                        <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(date("d/m/Y", $c["begins"]))?></td>
                        <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(date("d/m/Y", $c["ends"]))?></td>
                        <td class='mdl-data-table__cell--non-numeric'>
                          <a href='editcalendar.php?id=<?=(int)$c['id']?>&view=1' title='Ver calendario'><i class='material-icons icon'>open_in_new</i></a>
                          <a href='editcalendar.php?id=<?=(int)$c['id']?>' title='Editar calendario'><i class='material-icons icon'>edit</i></a>
                          <a href='dynamic/deletecalendar.php?id=<?=(int)$c['id']?>' data-dyndialog-href='dynamic/deletecalendar.php?id=<?=(int)$c['id']?>' title='Eliminar calendario'><i class='material-icons icon'>delete</i></a>
                          <a href='dynamic/exportcalendar.php?id=<?=(int)$c['id']?>' data-dyndialog-href='dynamic/exportcalendar.php?id=<?=(int)$c['id']?>' title='Exportar calendario'><i class='material-icons icon'>code</i></a>
                        </td>
                      </tr>
                      <?php
                    }
                    ?>
                  </tbody>
                </table>
              </div>
              <?php
            } else {
              echo "<p>No se ha configurado ningún calendario todavía.</p>";
            }
          }
          ?>

          <?php visual::printDebug("calendars::getAll()", $calendars_response); ?>
        </div>
      </div>
    </main>
  </div>

  <div class="mdl-snackbar mdl-js-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button type="button" class="mdl-snackbar__action"></button>
  </div>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["added", "Se ha añadido el calendario correctamente."],
    ["modified", "Se ha modificado el calendario correctamente."],
    ["deleted", "Se ha eliminado el calendario correctamente."],
    ["empty", "Faltan datos por introducir en el formulario o el correo electrónico es incorrecto."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["overlap", "El calendario que intentabas añadir se solapa con uno ya existente, así que no se ha añadido."]
  ], 10000, false);
  ?>
</body>
</html>
