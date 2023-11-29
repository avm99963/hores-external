<?php
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
<?php
visual::renderTooltips();
?>
