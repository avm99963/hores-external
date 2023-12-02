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
security::checkType(security::HYPERADMIN, security::METHOD_NOTFOUND);

if (!security::checkParams("GET", [
  ["place", security::PARAM_ISINT]
])) {
  security::notFound();
}

$place = $_GET["place"];
if (!in_array($place, help::$places)) security::notFound();

$url = help::get($place);
?>

<form action="dosethelpresource.php" method="POST" autocomplete="off">
  <input type="hidden" name="place" value="<?=(int)$place?>">
  <h4 class="mdl-dialog__title">Enlace de ayuda</h4>
  <div class="mdl-dialog__content">
    <p><b>Lugar:</b> <?=security::htmlsafe(help::$placesName[$place] ?? "undefined")?></b></p>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="url" name="url" id="url" autocomplete="off"<?=($url !== false ? ' value="'.security::htmlsafe($url).'"' : '')?>>
      <label class="mdl-textfield__label" for="url">URL</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary">Configurar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
