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
secondFactor::checkAvailability();

if (!isset($_GET["id"])) security::notFound();
$id = (int)$_GET["id"];

$s = secondFactor::getSecurityKeyById($id);
if ($s === false || people::userData("id") != $s["person"]) security::notFound();
?>

<form action="dodeletesecuritykey.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$s["id"]?>">
  <h4 class="mdl-dialog__title">Eliminar llave de seguridad</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar la llave de seguridad <b><?=security::htmlsafe($s["name"])?></b>? <span style="color:#EF5350;font-weight:bold;">Una vez la elimines no tendrás la opción de escogerla como segundo factor.</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
