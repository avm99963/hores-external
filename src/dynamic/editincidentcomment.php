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

$isAdmin = security::isAllowed(security::ADMIN);

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$incident = incidents::get($id);
if ($incident === false) security::notFound();

if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);

$status = incidents::getStatus($incident);
$cantedit = (in_array($status, incidents::$cannotEditCommentsStates) || !$isAdmin);
?>

<form action="<?=(!$isAdmin ? "doeditworkerincidentcomment.php" : "doeditincidentcomment.php")?>" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$incident["id"]?>">
  <h4 class="mdl-dialog__title">Observaciones incidencia</h4>
  <div class="mdl-dialog__content">
    <h5>Observaciones</h5>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <textarea class="mdl-textfield__input" name="details" id="details"<?=($cantedit ? " disabled" : "")?>><?=security::htmlsafe($incident["details"])?></textarea>
      <label class="mdl-textfield__label" for="details">Observaciones (opcional)</label>
    </div>

    <h5>Observaciones del trabajador</h5>
    <p><?=security::htmlsafe((!empty($incident["workerdetails"]) ? $incident["workerdetails"] : "-"))?></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary"<?=($cantedit ? " disabled" : "")?>>Modificar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
