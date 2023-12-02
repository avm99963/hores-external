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
  ["incidents", security::PARAM_ISARRAY]
])) {
  security::notFound();
}
?>

<style>
#dynDialog, #dynDialog .mdl-dialog__content {
  background: #FFCDD2;
}
</style>

<form action="dodeleteincidentsbulk.php" method="POST" autocomplete="off">
  <?php
  foreach ($_GET["incidents"] as $incident) {
    echo "<input type='hidden' name='incidents[]' value='".(int)$incident."'></li>";
  }
  ?>
  <h4 class="mdl-dialog__title">Eliminar/invalidar incidencias</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar/invalidar estas incidencias? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
    <p>Dependiendo del estado de cada incidencia, esta se eliminará o se invalidará.</p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
