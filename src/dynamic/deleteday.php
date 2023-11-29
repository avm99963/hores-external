<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

if (!schedules::dayExists($id)) {
  security::notFound();
}
?>

<form action="dodeleteday.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$id?>">
  <h4 class="mdl-dialog__title">Eliminar horario</h4>
  <div class="mdl-dialog__content">
    <p>¿Estás seguro que quieres eliminar este horario diario? <span style="color:#EF5350;font-weight:bold;">Esta acción es irreversible</span></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Eliminar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
