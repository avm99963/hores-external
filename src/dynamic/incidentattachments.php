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

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$incident = incidents::get($id, true);
if ($incident === false) security::notFound();

$isAdmin = security::isAllowed(security::ADMIN);
$status = incidents::getStatus($incident);

$cantedit = in_array($status, incidents::$cannotEditCommentsStates);

if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);
?>

<dynscript>
document.querySelectorAll(".deleteattachment").forEach(el => {
  el.addEventListener("click", e => {
    dynDialog.load("dynamic/deleteattachment.php?id="+el.getAttribute("data-id")+"&name="+el.getAttribute("data-name")<?=(isset($_GET["continue"]) ? '+"&continue='.security::htmlsafe(urlencode($_GET["continue"])).'"' : '')?>);
  });
});
</dynscript>

<style>
#dynDialog {
  max-width: 380px;
  width: auto;
}

.addAttachmentForm {
  display: flex;
  align-items: center;
}

.addAttachmentForm input[type="file"] {
  width: 100%;
  height: min-content;
}

.addAttachmentForm button {
  min-width: min-content;
}

.attachmentDescription {
  margin-top: 16px;
}

.attachmentDescription code {
  font-size: 12px;
}
</style>

<h4 class="mdl-dialog__title">Archivos adjuntos</h4>
<div class="mdl-dialog__content">
  <?php
  $attachments = incidents::getAttachmentsFromIncident($incident);

  if ($attachments === false) {
    echo "<p>Ha ocurrido un problema cargando los archivos adjuntos.</p>";
  } elseif (!count($attachments)) {
    echo "<p>No hay ningún archivo adjunto</p>";
  } else {
    echo '<ul class="mdl-list">';
    foreach ($attachments as $attachment) {
      $extension = files::getFileExtension($attachment);
      $icon = files::$mimeTypesIcons[$extension] ?? "broken_image";
      $title = files::$readableMimeTypes[$extension] ?? "Documento desconocido";
      echo '<li class="mdl-list__item">
        <span class="mdl-list__item-primary-content">
          <i class="material-icons mdl-list__item-icon">'.security::htmlsafe($icon).'</i>
          '.security::htmlsafe($title).'
        </span>
        <a href="incidentattachment.php?id='.(int)$incident["id"].'&name='.security::htmlsafe($attachment).'" target="_blank" class="mdl-list__item-secondar-action mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
          <i class="material-icons">open_in_new</i>
        </a>'.
        ($cantedit ? '' : '<button class="mdl-list__item-secondar-action mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect deleteattachment" data-id="'.(int)$id.'" data-name="'.security::htmlsafe($attachment).'">
          <i class="material-icons">delete</i>
        </button>').'
      </li>';
    }
    echo "</ul>";
  }

  if (!$cantedit) {
    ?>
    <h5>Añade un archivo adjunto</h5>
    <form action="doaddincidentattachment.php" method="POST" enctype="multipart/form-data" class="addAttachmentForm">
      <input type="hidden" name="id" value="<?=(int)$incident["id"]?>">
      <?php visual::addContinueInput(); ?>
      <input type="file" name="file" accept="<?=security::htmlsafe(files::getAcceptAttribute())?>" required>
      <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised">Subir</button>
    </form>
    <div class="attachmentDescription">Se aceptan archivos de hasta <?=security::htmlsafe(files::READABLE_MAX_SIZE)?> con los siguientes formatos: <code><?=security::htmlsafe(files::getAcceptAttribute(true))?></code></div>
    <?php
  }
  ?>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Cerrar</button>
</div>
