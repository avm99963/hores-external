<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$p = people::get($_GET["id"]);

if ($p === false) {
  security::notFound();
}
?>

<form action="doedituser.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title">Edita persona</h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="id" id="edit_id" value="<?=security::htmlsafe($p['id'])?>" readonly="readonly" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_nombre">ID</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="username" id="edit_username" value="<?=security::htmlsafe($p['username'])?>" autocomplete="off" data-required>
      <label class="mdl-textfield__label" for="edit_username">Nombre de usuario</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="edit_name"  value="<?=security::htmlsafe($p['name'])?>" autocomplete="off" data-required>
      <label class="mdl-textfield__label" for="edit_name">Nombre</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="dni" id="edit_dni"  value="<?=security::htmlsafe($p['dni'])?>" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_dni">DNI (opcional)</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="email" name="email" id="edit_email"  value="<?=security::htmlsafe($p['email'])?>" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_email">Correo electrónico (opcional)</label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="category" id="edit_category" class="mdlext-selectfield__select">
        <option value="-1"></option>
        <?php
        $categories = categories::getAll();
        foreach ($categories as $id => $category) {
          $selected = ($id == $p["categoryid"] ? " selected" : "");
          echo '<option value="'.(int)$id.'"'.$selected.'>'.security::htmlsafe($category).'</option>';
        }
        ?>
      </select>
      <label for="edit_category" class="mdlext-selectfield__label">Categoría (opcional)</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="password" name="password" id="edit_password" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_password">Contraseña</label>
    </div>
    <p><?=security::htmlsafe(security::$passwordHelperText)?></p>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="type" id="edit_type" class="mdlext-selectfield__select" data-required>
        <?php
        foreach (security::$types as $i => $type) {
          $selected = ($i == $p["type"] ? " selected" : "");
          echo '<option value="'.(int)$i.'"'.$selected.(security::isAllowed($i) ? "" : " disabled").'>'.security::htmlsafe($type).'</option>';
        }
        ?>
      </select>
      <label for="edit_type" class="mdlext-selectfield__label">Tipo</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Confirmar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
