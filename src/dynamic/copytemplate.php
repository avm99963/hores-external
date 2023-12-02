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

require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!security::checkParams("GET", [
  ["workers", security::PARAM_ISARRAY]
])) {
  security::notFound();
}
?>

<form action="docopytemplate.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title">Copiar plantilla a trabajadores</h4>
  <div class="mdl-dialog__content">
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="template" id="template" class="mdlext-selectfield__select" data-required>
        <?php
        $templates = schedules::getTemplates();
        foreach ($templates as $t) {
          echo '<option value="'.(int)$t["id"].'">'.security::htmlsafe($t["name"]).'</option>';
        }
        ?>
      </select>
      <label for="template" class="mdlext-selectfield__label">Plantilla</label>
    </div>
    <br>
    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="active">
      <input type="checkbox" id="active" name="active" value="1" class="mdl-switch__input">
      <span class="mdl-switch__label">Activar horario</span>
    </label>
    <br><br>
    <b>Copiar a:</b>
    <div class="copyto">
      <ul>
        <?php
        foreach ($_GET["workers"] as $workerid) {
          $worker = workers::get($workerid);
          if ($worker === false) {
            die("Error: Uno de los trabajadores seleccionados ya no existe");
          }

          echo "<li><input type='hidden' name='workers[]' value='".(int)$worker["id"]."'> ".security::htmlsafe($worker["name"])." (".security::htmlsafe($worker["companyname"]).")</li>";
        }
        ?>
      </ul>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Copiar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
