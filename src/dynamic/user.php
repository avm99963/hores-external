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

$p = people::get($_GET["id"], false);

if ($p === false) {
  security::notFound();
}

$companies = companies::getAll();
$pcompanies = [];

foreach($p["companies"] as $company) {
  $pcompanies[] = $companies[$company];
}

$secondFactor = secondFactor::isEnabled($p["id"]);

if ($secondFactor) {
?>
<dynscript>
document.querySelector(".disable-second-factor").addEventListener("click", e => {
  dynDialog.load("dynamic/disablesecondfactor.php?id=<?=(int)$p["id"]?>");
});
</dynscript>
<?php //do-not-add-license-header-here
}
?>

<style>
#dynDialog {
  max-width: 380px;
  width: auto;
}
</style>

<h4 class="mdl-dialog__title"><?=security::htmlsafe($p["name"])?></h4>
<ul>
  <li><b>Nombre de usuario:</b> <?=security::htmlsafe($p["username"])?></li>
  <li><b>DNI:</b> <?=(!empty($p["dni"]) ? security::htmlsafe($p["dni"]) : "-")?></li>
  <li><b>Correo electrónico:</b> <?=(!empty($p["email"]) ? "<a href=\"mailto:".security::htmlsafe(rawurlencode($p["email"]))."\" target=\"_blank\">".security::htmlsafe($p["email"])."</a>" : "-")?>
  <li><b>Categoría:</b> <?=($p["categoryid"] == -1 ? "-" : security::htmlsafe($p["category"]))?></li>
  <li><b>Dada de baja:</b> <?=($p["baixa"] == 1 ? visual::YES : "No")?></li>
  <li><b>Empresas:</b> <?=security::htmlsafe((count($p["companies"]) ? implode(", ", $pcompanies) : "-"))?></li>
  <li><b>Tipo de usuario:</b> <?=security::htmlsafe(security::$types[$p["type"]])?></li>
  <?php if (secondFactor::isAvailable()) { ?><li><b>Verificación en dos pasos:</b> <?=($secondFactor ? 'activada <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent disable-second-factor">Desactivar</button>' : 'desactivada')?></li><?php } ?>
</ul>

<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cerrar</button>
  <a href="userregistry.php?id=<?=(int)$p["id"]?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons">list</i><span class="mdl-ripple"></span></a>
  <a href="userincidents.php?id=<?=(int)$p["id"]?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons">assignment_late</i><span class="mdl-ripple"></span></a>
  <a href="workerschedule.php?id=<?=(int)$p["id"]?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons">timelapse</i><span class="mdl-ripple"></span></a>
</div>
