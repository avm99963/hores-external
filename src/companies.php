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
  .addcompany {
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
    <button class="addcompany mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Empresas</h2>
          <?php
          $companies = companies::getAll(false);
          if (count($companies)) {
            ?>
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <thead>
                  <tr>
                    <th class="extra">ID</th>
                    <th class="mdl-data-table__cell--non-numeric">Empresa</th>
                    <th class="mdl-data-table__cell--non-numeric">CIF</th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($companies as $c) {
                    ?>
                    <tr>
                      <td class="extra"><?=(int)$c["id"]?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($c["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=(empty($c["cif"]) ? "-" : security::htmlsafe($c["cif"]))?></td>
                      <td class='mdl-data-table__cell--non-numeric'><a href='dynamic/editcompany.php?id=<?=(int)$c["id"]?>' data-dyndialog-href='dynamic/editcompany.php?id=<?=(int)$c["id"]?>' title='Editar empresa'><i class='material-icons icon'>edit</i></a></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
          } else {
            ?>
            <p>Todavía no hay definida ninguna empresa.</p>
            <p>Puedes añadir una haciendo clic en el botón de la esquina inferior derecha de la página.</p>
            <?php
          }
          ?>

          <?php visual::printDebug("companies::getAll()", $companies); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addcompany">
    <form action="doaddcompany.php" method="POST" autocomplete="off">
      <h4 class="mdl-dialog__title">Añade una empresa</h4>
      <div class="mdl-dialog__content">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre de la empresa</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="cif" id="cif" autocomplete="off">
          <label class="mdl-textfield__label" for="cif">CIF (opcional)</label>
        </div>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
        <button onclick="event.preventDefault(); document.querySelector('#addcompany').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::smartSnackbar([
    ["added", "Se ha añadido la empresa correctamente."],
    ["modified", "Se ha modificado la empresa correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>

  <script src="js/companies.js"></script>
</body>
</html>
