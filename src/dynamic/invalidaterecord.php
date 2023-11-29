<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$record = registry::get($id);
if ($record === false || $record["invalidated"] != 0) security::notFound();

$isAdmin = security::isAllowed(security::ADMIN);
if (!$isAdmin) registry::checkRecordIsFromPerson($record["id"]);
?>

<form action="doinvalidaterecord.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <h4 class="mdl-dialog__title">Invalidar elemento del registro</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar este elemento del registro? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Invalidar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
