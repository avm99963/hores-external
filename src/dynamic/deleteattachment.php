<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT],
  ["name", security::PARAM_NEMPTY]
])) {
  security::notFound();
}

$id = (int)$_GET["id"];
$name = $_GET["name"];

$incident = incidents::get($id, true);
if ($incident === false) security::notFound();

if (!security::isAllowed(security::ADMIN)) incidents::checkIncidentIsFromPerson($incident["id"]);

$attachments = incidents::getAttachmentsFromIncident($incident);

if ($attachments === false || !count($attachments)) security::notFound();

$flag = false;

foreach ($attachments as $attachment) {
  if ($attachment == $name) {
    $flag = true;
    ?>
    <form action="dodeleteattachment.php" method="POST" autocomplete="off">
      <input type="hidden" name="id" value="<?=(int)$id?>">
      <?php visual::addContinueInput(); ?>
      <input type="hidden" name="name" value="<?=security::htmlsafe($name)?>">
      <h4 class="mdl-dialog__title">Eliminar archivo adjunto</h4>
      <div class="mdl-dialog__content">
        <p>¿Estás seguro que quieres eliminar el archivo adjunto <code><?=security::htmlsafe($name)?></code>? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
        <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
    <?php
    break;
  }
}

if ($flag === false) security::notFound();
