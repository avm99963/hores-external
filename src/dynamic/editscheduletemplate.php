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

if (!isset($_GET["id"])) {
  security::notFound();
}

$t = schedules::getTemplate($_GET["id"]);

if ($t === false) {
  security::notFound();
}
?>

<form action="doeditscheduletemplate.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$t["id"]?>">
  <h4 class="mdl-dialog__title">Editar plantilla</h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" value="<?=security::htmlsafe($t["name"])?>" data-required>
      <label class="mdl-textfield__label" for="name">Nombre de la plantilla</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off"  value="<?=security::htmlsafe(date("Y-m-d", $t["begins"]))?>" data-required>
      <label class="mdl-textfield__label always-focused" for="begins">Fecha inicio de validez del horario</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" value="<?=security::htmlsafe(date("Y-m-d", $t["ends"]))?>" data-required>
      <label class="mdl-textfield__label always-focused" for="ends">Fecha fin de validez del horario</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Confirmar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
