<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$c = categories::get($_GET["id"]);

if ($c === false) {
  security::notFound();
}
?>

<form action="doeditcategory.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title">Editar categoría</h4>
  <div class="mdl-dialog__content">
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="id" id="edit_id" value="<?=security::htmlsafe($c['id'])?>" readonly="readonly" autocomplete="off">
      <label class="mdl-textfield__label" for="edit_nombre">ID</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="name" id="edit_name"  value="<?=security::htmlsafe($c['name'])?>" autocomplete="off" data-required>
      <label class="mdl-textfield__label" for="edit_name">Nombre de la categoría</label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="parent" id="parent" class="mdlext-selectfield__select">
        <option value="0"></option>
        <?php
        foreach (categories::getAll(false) as $category) {
          if ($category["parent"] == 0 && $category["id"] != $c["id"]) {
            echo '<option value="'.$category["id"].'"'.($c["parent"] == $category["id"] ? "selected" : "").'>'.$category["name"].'</option>';
          }
        }
        ?>
      </select>
      <label for="parent" class="mdlext-selectfield__label">Categoría padre</label>
    </div>
    <br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <textarea class="mdl-textfield__input" name="emails" id="emails"><?=security::htmlsafe(categories::readableEmails($c["emails"]))?></textarea>
      <label class="mdl-textfield__label" for="emails">Correos electrónicos de los responsables</label>
    </div>
    <span style="font-size: 12px;">Introduce los correos separados por comas.</span>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Confirmar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
