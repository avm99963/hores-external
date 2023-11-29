<?php
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
document.getElementById("cancel").addEventListener("click", e => {
  e.preventDefault();
  dynDialog.load("dynamic/workhistory.php?id="+parseInt(document.getElementById("cancel").getAttribute("data-worker-id")));
});
</dynscript>

<form action="doaddworkhistoryitem.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$worker["id"]?>">
  <h4 class="mdl-dialog__title">Añadir alta/baja</h4>
  <div class="mdl-dialog__content">
    <p><b>Persona:</b> <?=security::htmlsafe($worker["name"])?><br>
    <b>Empresa:</b> <?=security::htmlsafe($worker["companyname"])?></p>

    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="day" id="day" autocomplete="off" data-required>
      <label class="mdl-textfield__label" for="day">Fecha</label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="status" id="status" class="mdlext-selectfield__select" data-required>
        <option></option>
        <?php
        foreach (workers::$affiliationStatusesManual as $status) {
          echo '<option value="'.(int)$status.'">'.security::htmlsafe(workers::affiliationStatusHelper($status)).'</option>';
        }
        ?>
      </select>
      <label for="status" class="mdlext-selectfield__label">Tipo</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
    <button id="cancel" class="mdl-button mdl-js-button mdl-js-ripple-effect" data-worker-id="<?=(int)$worker["id"]?>">Cancelar</button>
  </div>
</form>
