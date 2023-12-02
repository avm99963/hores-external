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
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$incident = incidents::get($id, true);
if ($incident === false) security::notFound();

$isAdmin = security::isAdminView();
$status = incidents::getStatus($incident);

if (($isAdmin && in_array($status, incidents::$cannotEditStates)) || (!$isAdmin && !in_array($status, incidents::$workerCanEditStates))) security::notFound();
if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);
?>

<dynscript>
document.getElementById("edit_allday").addEventListener("change", e => {
  var partialtime = document.getElementById("edit_partialtime");
  if (e.target.checked) {
    partialtime.classList.add("notvisible");
  } else {
    partialtime.classList.remove("notvisible");
  }
});
</dynscript>

<form action="doeditincident.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <?php visual::addContinueInput(); ?>
  <h4 class="mdl-dialog__title">Editar incidencia</h4>
  <div class="mdl-dialog__content">
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="type" id="edit_type" class="mdlext-selectfield__select" data-required>
        <option></option>
        <?php
        foreach (incidents::getTypesForm() as $i) {
          echo '<option value="'.(int)$i["id"].'"'.($i["id"] == $incident["type"] ? " selected" : "").'>'.security::htmlsafe($i["name"]).'</option>';
        }
        ?>
      </select>
      <label for="edit_type" class="mdlext-selectfield__label">Tipo</label>
    </div>

    <h5>Afectación</h5>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="day" id="edit_day" autocomplete="off" value="<?=security::htmlsafe(date("Y-m-d", $incident["day"]))?>" data-required>
      <label class="mdl-textfield__label always-focused" for="edit_day">Día</label>
    </div>
    <br>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="edit_allday">
        <input type="checkbox" id="edit_allday" name="allday" value="1" class="mdl-switch__input"<?=($incident["allday"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Día entero</span>
      </label>
    </p>
    <div id="edit_partialtime"<?=($incident["allday"] ? ' class="notvisible"' : '')?>>De <input type="time" name="begins"<?=($incident["allday"] ? '' : ' value="'.schedules::sec2time($incident["begins"]).'"')?>> a <input type="time" name="ends"<?=($incident["allday"] ? '' : ' value="'.schedules::sec2time($incident["ends"]).'"')?>></div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Editar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
