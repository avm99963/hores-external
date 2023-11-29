<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!security::checkParams("GET", [
  ["workers", security::PARAM_ISARRAY]
])) {
  security::notFound();
}
?>

<style>
.notvisible {
  display: none;
}
</style>

<dynscript>
document.getElementById("allday").addEventListener("change", e => {
  var partialtime = document.getElementById("partialtime");
  if (e.target.checked) {
    partialtime.classList.add("notvisible");
  } else {
    partialtime.classList.remove("notvisible");
  }
});
</dynscript>

<form action="doaddincidentbulk.php" method="POST" autocomplete="off">
  <h4 class="mdl-dialog__title">Añade una incidencia</h4>
  <div class="mdl-dialog__content">
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select name="type" id="type" class="mdlext-selectfield__select" data-required>
        <option></option>
        <?php
        foreach (incidents::getTypesForm() as $i) {
          echo '<option value="'.(int)$i["id"].'">'.security::htmlsafe($i["name"]).'</option>';
        }
        ?>
      </select>
      <label for="type" class="mdlext-selectfield__label">Tipo</label>
    </div>

    <h5>Afectación</h5>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="date" name="day" id="day" autocomplete="off" data-required>
      <label class="mdl-textfield__label always-focused" for="day">Día</label>
    </div>
    <br>
    <p>
      <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="allday">
        <input type="checkbox" id="allday" name="allday" value="1" class="mdl-switch__input">
        <span class="mdl-switch__label">Día entero</span>
      </label>
    </p>
    <div id="partialtime">De <input type="time" name="begins"> a <input type="time" name="ends"></div>

    <h5>Detalles adicionales</h5>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <textarea class="mdl-textfield__input" name="details" id="details"></textarea>
      <label class="mdl-textfield__label" for="details">Observaciones (opcional)</label>
    </div>
    <p>Las observaciones aparecerán en los PDFs que se exporten.</p>
    <p>Después de crear la incidencia podrás añadir archivos adjuntos haciendo clic en el botón <i class="material-icons" style="vertical-align: middle;">attach_file</i>.</p>

    <b>Trabajadores:</b>
    <div class="copyto">
      <ul>
        <?php
        foreach ($_GET["workers"] as $workerid) {
          $worker = workers::get($workerid);
          if ($worker === false) {
            die("Error: Uno de los trabajadores seleccionados ya no existe");
          }

          echo "<li><input type='hidden' name='workers[]' value='".(int)$worker["id"]."'> ".security::htmlsafe($worker["name"])." (".security::htmlsafe($worker["companyname"]).")</li>";
        }
        ?>
      </ul>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
