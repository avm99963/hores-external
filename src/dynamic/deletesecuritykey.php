<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (!isset($_GET["id"])) security::notFound();
$id = (int)$_GET["id"];

$s = secondFactor::getSecurityKeyById($id);
if ($s === false || people::userData("id") != $s["person"]) security::notFound();
?>

<form action="dodeletesecuritykey.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$s["id"]?>">
  <h4 class="mdl-dialog__title">Eliminar llave de seguridad</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar la llave de seguridad <b><?=security::htmlsafe($s["name"])?></b>? <span style="color:#EF5350;font-weight:bold;">Una vez la elimines no tendrás la opción de escogerla como segundo factor.</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
