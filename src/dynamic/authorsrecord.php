<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

$isAdmin = security::isAllowed(security::ADMIN);

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$record = registry::get($id, true);
if ($record === false) security::notFound();

if (!$isAdmin) registry::checkRecordIsFromPerson($record["id"]);
?>

<style>
#dynDialog {
  max-width: 380px;
  width: auto;
}
</style>

<h4 class="mdl-dialog__title">Autoría del elemento del registro</h4>
<div class="mdl-dialog__content">
  <ul>
    <li><b>Creador:</b> <?=($record["creator"] == -1 ? "<span style='font-family: monospace;'>cron</span>" : security::htmlsafe(people::userData("name", $record["creator"])))?></li>
    <li><b>Fecha de creación:</b> <?=date("d/m/Y H:i", $record["created"])?></li>
    <?php if ($record["invalidatedby"] != -1) { ?><li><b>Invalidado por:</b> <?=security::htmlsafe(people::userData("name", $record["invalidatedby"]))?></li><?php } ?>
    <?php
    if ($record["state"] == registry::STATE_VALIDATED_BY_WORKER) {
      $validation = json_decode($record["workervalidation"], true);
      validationsView::renderValidationInfo($validation);
    }
    ?>
  </ul>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Cerrar</button>
</div>
