<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$incident = incidents::get($id);
if ($incident === false) security::notFound();

$isAdmin = security::isAdminView();
$status = incidents::getStatus($incident);
if (($isAdmin && !in_array($status, incidents::$canRemoveStates)) || (!$isAdmin && !in_array($status, incidents::$workerCanRemoveStates))) security::notFound();
?>

<form action="dodeleteincident.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <?php visual::addContinueInput(); ?>
  <h4 class="mdl-dialog__title">Eliminar incidencia</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar esta incidencia? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
