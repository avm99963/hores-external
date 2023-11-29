<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$s = schedules::get($_GET["id"]);

if ($s === false) {
  security::notFound();
}
?>

<form action="doeditschedule.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$s["id"]?>">
  <h4 class="mdl-dialog__title">Editar horario</h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off"  value="<?=security::htmlsafe(date("Y-m-d", $s["begins"]))?>" data-required>
      <label class="mdl-textfield__label always-focused" for="begins">Fecha inicio de validez del horario</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" value="<?=security::htmlsafe(date("Y-m-d", $s["ends"]))?>" data-required>
      <label class="mdl-textfield__label always-focused" for="ends">Fecha fin de validez del horario</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Confirmar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
