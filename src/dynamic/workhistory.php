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

$worker = workers::get($id);
if ($worker === false) security::notFound();
?>

<dynscript>
document.getElementById("additem").addEventListener("click", e => {
  dynDialog.load("dynamic/addworkhistoryitem.php?id="+parseInt(document.getElementById("additem").getAttribute("worker-id")));
});

document.querySelectorAll(".edititem").forEach(el => {
  el.addEventListener("click", e => {
    dynDialog.load("dynamic/editworkhistoryitem.php?id="+parseInt(el.getAttribute("data-id")));
  });
});

document.querySelectorAll(".deleteitem").forEach(el => {
  el.addEventListener("click", e => {
    dynDialog.load("dynamic/deleteworkhistoryitem.php?id="+parseInt(el.getAttribute("data-id")));
  });
});
</dynscript>

<style>
#dynDialog {
  max-width: 380px;
  width: auto;
}

#dynDialog .mdl-list {
  margin-top: 0;
  padding-top: 0;
}

.float-right {
  float: right;
}
</style>

<h4 class="mdl-dialog__title">Historial de altas y bajas</h4>
<div class="mdl-dialog__content">
  <p><b>Persona:</b> <?=security::htmlsafe($worker["name"])?><br>
  <b>Empresa:</b> <?=security::htmlsafe($worker["companyname"])?></p>

  <div class="float-right"><button id="additem" class="mdl-button mdl-js-button mdl-js-ripple-effect" worker-id="<?=(int)$worker["id"]?>"><i class="material-icons">add</i> Añadir alta/baja</button></div>
  <div style="clear: both;"></div>

  <?php
  $items = workers::getWorkHistory($id);

  if ($items === false) {
    echo "<p>Ha ocurrido un problema cargando los elementos del historial.</p>";
  } elseif (!count($items)) {
    echo "<p>No hay ningún elmento en el historial, así que el aplicativo está considerando que el trabajador está de baja.</p>";
  } else {
    echo '<ul class="mdl-list">';
    foreach ($items as $item) {
      $icon = security::htmlsafe(workers::affiliationStatusIcon($item["status"]) ?? "indeterminate_check_box");
      $helper = workers::affiliationStatusHelper($item["status"]);
      $day = date("d/m/Y", $item["day"]);
      $isAutomatic = workers::isAutomaticAffiliation($item["status"]);
      echo '<li class="mdl-list__item '.($isAutomatic ? 'mdl-list__item--two-line' : '').'">
        <span class="mdl-list__item-primary-content">
          <i class="material-icons mdl-list__item-icon">'.security::htmlsafe($icon).'</i>
          <span>'.security::htmlsafe($helper).' ('.security::htmlsafe($day).')</span>
          '.($isAutomatic ? '<span class="mdl-list__item-sub-title">Registro automático</span>' : '').'
        </span>
        <button class="mdl-list__item-secondar-action mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect edititem" data-id="'.(int)$item["id"].'">
          <i class="material-icons">edit</i>
        </button>
        <button class="mdl-list__item-secondar-action mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect deleteitem" data-id="'.(int)$item["id"].'">
          <i class="material-icons">delete</i>
        </button>
      </li>';
    }
    echo "</ul>";
  }
  ?>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Cerrar</button>
</div>
