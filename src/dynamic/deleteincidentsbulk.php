<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!security::checkParams("GET", [
  ["incidents", security::PARAM_ISARRAY]
])) {
  security::notFound();
}
?>

<style>
#dynDialog, #dynDialog .mdl-dialog__content {
  background: #FFCDD2;
}
</style>

<form action="dodeleteincidentsbulk.php" method="POST" autocomplete="off">
  <?php
  foreach ($_GET["incidents"] as $incident) {
    echo "<input type='hidden' name='incidents[]' value='".(int)$incident."'></li>";
  }
  ?>
  <h4 class="mdl-dialog__title">Eliminar/invalidar incidencias</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar/invalidar estas incidencias? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
    <p>Dependiendo del estado de cada incidencia, esta se eliminará o se invalidará.</p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
