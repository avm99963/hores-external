<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$t = incidents::getType($_GET["id"]);

if ($t === false) {
  security::notFound();
}
?>

<form action="doeditincidenttype.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title">Edita tipo de incidencia</h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="id" id="edit_id" value="<?=security::htmlsafe($t['id'])?>" readonly="readonly" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_id">ID</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="e_name" value="<?=security::htmlsafe($t['name'])?>" autocomplete="off" data-required>
      <label class="mdl-textfield__label" for="e_name">Nombre del tipo de incidencia</label>
    </div>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_present">
        <input type="checkbox" id="e_present" name="present" value="1" class="mdl-switch__input" <?=($t["present"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Presente <i class="material-icons help" id="edit_present">help</i></span>
      </label>
      <div class="mdl-tooltip" for="edit_present">Márquese si el trabajador está físicamente presente en el espacio de trabajo durante la incidencia.</div>
    </p>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_paid">
        <input type="checkbox" id="e_paid" name="paid" value="1" class="mdl-switch__input" <?=($t["paid"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Remunerada <i class="material-icons help" id="edit_paid">help</i></span>
      </label>
      <div class="mdl-tooltip" for="edit_paid">Márquese si el trabajador es remunerado las horas que dura la incidencia.</div>
    </p>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_workerfill">
        <input type="checkbox" id="e_workerfill" name="workerfill" value="1" class="mdl-switch__input"<?=($t["workerfill"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Puede autorrellenarse <i class="material-icons help" id="edit_workerfill">help</i></span>
      </label>
    </p>
    <div class="mdl-tooltip" for="edit_workerfill">Márquese si se permite que el trabajador pueda rellenar una incidencia de este tipo él mismo (con la posterior verificación por parte de un administrador).</div>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_notifies">
        <input type="checkbox" id="e_notifies" name="notifies" value="1" class="mdl-switch__input"<?=($t["notifies"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Notifica <i class="material-icons help" id="edit_notifies">help</i></span>
      </label>
      <div class="mdl-tooltip" for="edit_notifies">Márquese si la introducción de una incidencia de este tipo notifica por correo electrónico a las personas especificadas en la categoría del trabajador.</div>
    </p>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_autovalidates">
        <input type="checkbox" id="e_autovalidates" name="autovalidates" value="1" class="mdl-switch__input"<?=($t["autovalidates"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Se autovalida <i class="material-icons help" id="edit_autovalidates">help</i></span>
      </label>
      <div class="mdl-tooltip" for="edit_autovalidates">Márquese si al introducir una incidencia de este tipo se quiere que se autovalide sin necesidad de ser validada posteriormente por el trabajador.</div>
    </p>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="e_hidden">
        <input type="checkbox" id="e_hidden" name="hidden" value="1" class="mdl-switch__input"<?=($t["hidden"] ? " checked" : "")?>>
        <span class="mdl-switch__label">Oculto</span>
      </label>
    </p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Confirmar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
