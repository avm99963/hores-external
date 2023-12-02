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

$helper = security::htmlsafe(strtolower(workers::affiliationStatusHelper($item["status"])));
?>

<dynscript>
document.getElementById("cancel").addEventListener("click", e => {
  e.preventDefault();
  dynDialog.load("dynamic/workhistory.php?id="+parseInt(document.getElementById("cancel").getAttribute("data-worker-id")));
});
</dynscript>

<form action="dodeleteworkhistoryitem.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <h4 class="mdl-dialog__title">Eliminar registro de <?=security::htmlsafe($helper)?></h4>

  <div class="mdl-dialog__content">
    <p><b>Persona:</b> <?=security::htmlsafe($worker["name"])?><br>
    <b>Empresa:</b> <?=security::htmlsafe($worker["companyname"])?><br>
    <b>Día:</b> <?=security::htmlsafe(date("d/m/Y", $item["day"]))?></p>

    <p>¿Estás seguro que quieres eliminar este registro de <?=security::htmlsafe($helper)?>? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button id="cancel" class="mdl-button mdl-js-button mdl-js-ripple-effect" data-worker-id="<?=(int)$worker["id"]?>">Cancelar</button>
  </div>
</form>
