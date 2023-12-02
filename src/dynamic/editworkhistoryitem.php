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

$id = (int)$_GET["id"];

$item = workers::getWorkHistoryItem($id);
if ($item === false) security::notFound();

$isHidden = workers::isHidden($item["status"]);

$worker = workers::get($item["worker"]);
if ($worker === false) security::notFound();
?>

<dynscript>
document.getElementById("cancel").addEventListener("click", e => {
  e.preventDefault();
  dynDialog.load("dynamic/workhistory.php?id="+parseInt(document.getElementById("cancel").getAttribute("data-worker-id")));
});
</dynscript>

<form action="doeditworkhistoryitem.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <h4 class="mdl-dialog__title">Editar <?=security::htmlsafe(strtolower(workers::affiliationStatusHelper($item["status"])))?></h4>
  <div class="mdl-dialog__content">
    <p><b>Persona:</b> <?=security::htmlsafe($worker["name"])?><br>
    <b>Empresa:</b> <?=security::htmlsafe($worker["companyname"])?></p>

    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="day" id="day" autocomplete="off" data-required value="<?=security::htmlsafe(date("Y-m-d", $item["day"]))?>">
      <label class="mdl-textfield__label" for="day">Fecha</label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="status" id="status" class="mdlext-selectfield__select" data-required>
        <option></option>
        <?php
        foreach (workers::$affiliationStatusesManual as $status) {
          $currentIsHidden = workers::isHidden($status);
          echo '<option value="'.(int)$status.'"'.((($isHidden && $currentIsHidden) || (!$isHidden && !$currentIsHidden)) ? ' selected' : '').'>'.security::htmlsafe(workers::affiliationStatusHelper($status)).'</option>';
        }
        ?>
      </select>
      <label for="status" class="mdlext-selectfield__label">Tipo</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Editar</button>
    <button id="cancel" class="mdl-button mdl-js-button mdl-js-ripple-effect" data-worker-id="<?=(int)$worker["id"]?>">Cancelar</button>
  </div>
</form>
