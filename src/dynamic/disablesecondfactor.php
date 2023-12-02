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

if (!secondFactor::isEnabled($id)) {
  security::notFound();
}

if (!security::isAllowed(security::ADMIN) && $id != people::userData("id")) security::notFound();

if ($id == people::userData("id")) {
?>
<style>
#dynDialog {
  max-width: 500px;
  width: auto;
}
</style>
<?php //do-not-add-license-header-here
}
?>

<form action="dodisablesecondfactor.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <h4 class="mdl-dialog__title">Desactivar la verificación en dos pasos</h4>
  <div class="mdl-dialog__content">
    <?php
    if ($id != people::userData("id")) {
      ?>
      <p>¿Estás seguro que quieres desactivar la verificación en dos pasos para <b><?=security::htmlsafe(people::userData("name", $id))?></b>?</p>
      <p>Esta acción solo debe tomarse cuando el trabajador no puede acceder a su cuenta, puesto que <span style="color:#EF5350;font-weight:bold;">esta acción solo la puede revertir el trabajador reactivando de nuevo la verificación en dos pasos</span></p>
      <?php
    } else {
      ?>
      <p>¿Estás seguro que quieres desactivar la verificación en dos pasos?</p>
      <p>La verificación en 2 pasos ofrece seguridad extra a tu cuenta. Si inhabilitas la verificación en 2 pasos todas tus llaves de seguridad se desvincularán de esta cuenta y no te pediremos ningún código de verificación al iniciar sesión.</p>
      <p>Si aun así sigues quiriendo desactivarla, introduce tu contraseña y haz clic en el botón Desactivar.</p>
      <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
        <input class="mdl-textfield__input" type="password" name="password" id="password" data-required>
        <label class="mdl-textfield__label" for="password">Contraseña actual</label>
      </div>
      <?php
    }
    ?>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Desactivar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
