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

$mdHeaderRowBefore = visual::backBtn("workers.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .addtemplate {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <button class="addtemplate mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Plantillas de horarios</h2>
          <?php
          $templates = schedules::getTemplates();
          if (count($templates)) {
              foreach ($templates as $t) {
              ?>
              <a href="scheduletemplate.php?id=<?=(int)$t["id"]?>" class="clicky-container">
                <div class="clicky mdl-js-ripple-effect">
                  <div class="text">
                    <span class="title"><?=security::htmlsafe($t["name"])?></span><br>
                    <span class="description"><?=security::htmlsafe(date("d/m/Y", $t["begins"]))?> - <?=security::htmlsafe(date("d/m/Y", $t["ends"]))?></span>
                  </div>
                  <div class="mdl-ripple"></div>
                </div>
              </a>
              <?php
            }
          } else {
            ?>
            <p>Todavía no has creado ninguna plantilla.</p>
            <p>Puedes añadir una haciendo clic en el botón de la esquina inferior derecha de la página.</p>
            <?php
          }
          ?>

          <?php visual::printDebug("schedules::getTemplates()", $templates); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addtemplate">
    <form action="doaddscheduletemplate.php" method="POST" autocomplete="off">
      <h4 class="mdl-dialog__title">Crea una nueva plantilla</h4>
      <div class="mdl-dialog__content">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre de la plantilla</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off" data-required>
          <label class="mdl-textfield__label always-focused" for="begins">Fecha inicio de validez del horario</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" data-required>
          <label class="mdl-textfield__label always-focused" for="ends">Fecha fin de validez del horario</label>
        </div>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Crear</button>
        <button onclick="event.preventDefault(); document.querySelector('#addtemplate').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::smartSnackbar([
    ["added", "Se ha añadido la plantilla correctamente."],
    ["deleted", "Se ha eliminado la plantilla correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["order", "La fecha de inicio debe ser anterior a la fecha de fin."]
  ]);
  ?>

  <script src="js/scheduletemplates.js"></script>
</body>
</html>
