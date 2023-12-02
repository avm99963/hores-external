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

$log = registry::getLog($id);
if ($log === false) security::notFound();
?>

<style>
#dynDialog {
  max-width: 500px;
  width: auto;
}

.log {
  white-space: pre-wrap;
}
</style>

<h4 class="mdl-dialog__title">
  Log
  <?php
  if ($log["warningpos"] > 0) {
    visual::addTooltip("warning", "El log contiene mensajes de advertencia");
    ?>
    <i class="material-icons mdl-color-text--orange help" id="warning">warning</i>
    <?php
  }

  if ($log["errorpos"] > 0) {
    visual::addTooltip("error", "El log contiene mensajes de error");
    ?>
    <i class="material-icons mdl-color-text--red help" id="error">error</i>
    <?php
  }

  if ($log["fatalerrorpos"] > 0) {
    visual::addTooltip("fatalerror", "El log contiene errores fatales");
    ?>
    <i class="material-icons mdl-color-text--red help-900" id="fatalerror">error</i>
    <?php
  }
  ?>
</h4>
<div class="mdl-dialog__content">
  <pre class="log"><?=registry::beautifyLog(security::htmlsafe($log["logdetails"]))?></pre>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary">Cerrar</button>
</div>
<?php //do-not-add-license-header-here
visual::renderTooltips();
?>
