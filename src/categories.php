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
  .addcategory {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
  }

  @media (max-width: 655px) {
    .extra {
      display: none;
    }
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <button class="addcategory mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Categorías</h2>
          <?php
          $categories = categories::getAll(false);
          if (count($categories)) {
            ?>
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <thead>
                  <tr>
                    <th class="extra">ID</th>
                    <th class="mdl-data-table__cell--non-numeric">Categoría</th>
                    <th class="mdl-data-table__cell--non-numeric extra">Emails responsables <i id="tt_emails" class="material-icons help">help</i></th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($categories as $c) {
                    $emails = categories::readableEmails($c["emails"]);
                    ?>
                    <tr>
                      <td class="extra"><?=(int)$c["id"]?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($c["name"]).($c["parent"] == 0 ? "" : "<br><span class='mdl-color-text--grey-600'>Padre: ".security::htmlsafe($c["parentname"])."</span>")?></td>
                      <td class="mdl-data-table__cell--non-numeric extra"><?=security::htmlsafe((empty($emails) ? "-" : $emails))?></td>
                      <td class='mdl-data-table__cell--non-numeric'><a href='dynamic/editcategory.php?id=<?=(int)$c["id"]?>' data-dyndialog-href='dynamic/editcategory.php?id=<?=(int)$c["id"]?>' title='Editar categoría'><i class='material-icons icon'>edit</i></a></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php visual::addTooltip("tt_emails", "Cuando un tipo de incidencia tenga activada las notificaciones a los responsables de categoría, se notificará de las incidencias nuevas a estos correos."); ?>
            <?php
          } else {
            ?>
            <p>Todavía no hay definida ninguna categoría para los trabajadores.</p>
            <p>Puedes añadir una haciendo clic en el botón de la esquina inferior derecha de la página.</p>
            <?php
          }
          ?>

          <?php visual::printDebug("categories::getAll()", $categories); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addcategory">
    <form action="doaddcategory.php" method="POST" autocomplete="off">
      <h4 class="mdl-dialog__title">Añade una categoría</h4>
      <div class="mdl-dialog__content">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre de la categoría</label>
        </div>
        <br>
        <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
          <select name="parent" id="parent" class="mdlext-selectfield__select">
            <option value="0"></option>
            <?php
            foreach ($categories as $category) {
              if ($category["parent"] == 0) echo '<option value="'.$category["id"].'">'.$category["name"].'</option>';
            }
            ?>
          </select>
          <label for="parent" class="mdlext-selectfield__label">Categoría padre</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <textarea class="mdl-textfield__input" name="emails" id="emails"></textarea>
          <label class="mdl-textfield__label" for="emails">Correos electrónicos de los responsables</label>
        </div>
        <span style="font-size: 12px;">Introduce los correos separados por comas.</span>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
        <button onclick="event.preventDefault(); document.querySelector('#addcategory').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["added", "Se ha añadido la categoría correctamente."],
    ["modified", "Se ha modificado la categoría correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>

  <script src="js/categories.js"></script>
</body>
</html>
