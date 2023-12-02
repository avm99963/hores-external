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

$incident = incidents::get($id, true);
if ($incident === false) security::notFound();

if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);
?>

<style>
#dynDialog {
  max-width: 380px;
  width: auto;
}
</style>

<h4 class="mdl-dialog__title">Autoría de la incidencia</h4>
<div class="mdl-dialog__content">
  <ul>
    <?php if ($incident["creator"] != -1) { ?><li><b>Creador:</b> <?=security::htmlsafe(people::userData("name", $incident["creator"]))?></li><?php } ?>
    <?php if ($incident["updatedby"] != -1) { ?><li><b>Última modificación por:</b> <?=security::htmlsafe(people::userData("name", $incident["updatedby"]))?></li><?php } ?>
    <?php if ($incident["confirmedby"] != -1) { ?><li><b>Revisor:</b> <?=security::htmlsafe(people::userData("name", $incident["confirmedby"]))?></li><?php } ?>
    <?php
    if ($incident["state"] == incidents::STATE_VALIDATED_BY_WORKER) {
      $validation = json_decode($incident["workervalidation"], true);
      validationsView::renderValidationInfo($validation);
    }
    ?>
  </ul>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Cerrar</button>
</div>
