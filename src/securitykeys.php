<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

$mdHeaderRowBefore = visual::backBtn("security.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .addsecuritykey {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <button class="addsecuritykey mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Llaves de seguridad</h2>
          <?php
          $securityKeys = secondFactor::getSecurityKeys();
          if ($securityKeys === false) {
            echo "<p>Ha ocurrido un error inesperado y no se ha podido obtener un listado de tus llaves de seguridad.</p>";
          } elseif (count($securityKeys)) {
            ?>
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <thead>
                  <tr>
                    <th class="mdl-data-table__cell--non-numeric">Nombre</th>
                    <th class="mdl-data-table__cell--non-numeric">Fecha de registro</th>
                    <th class="mdl-data-table__cell--non-numeric">Último uso</th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($securityKeys as $s) {
                    ?>
                    <tr>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($s["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($s["added"] !== null ? date("d/m/Y H:i", $s["added"]) : "-")?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($s["lastused"] !== null ? date("d/m/Y H:i", $s["lastused"]) : "-")?></td>
                      <td class='mdl-data-table__cell--non-numeric'><a href='dynamic/deletesecuritykey.php?id=<?=(int)$s["id"]?>' data-dyndialog-href='dynamic/deletesecuritykey.php?id=<?=(int)$s["id"]?>' title='Eliminar llave de seguridad'><i class='material-icons icon'>delete</i></a></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
          } else {
            ?>
            <p>Todavía no has añadido ninguna llave de seguridad.</p>
            <p>Puedes añadir una haciendo clic en el botón de la esquina inferior derecha de la página.</p>
            <?php
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addsecuritykey">
    <form method="POST" id="addsecuritykeyform">
      <h4 class="mdl-dialog__title">Añadir llave de seguridad</h4>
      <div class="mdl-dialog__content">
        <p>Introduce un nombre para la llave de seguridad y haz clic en el botón añadir para empezar el proceso de registro:</p>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre</label>
        </div>
      </div>
      <div class="mdl-dialog__actions">
        <button id="registersecuritykey" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Registrar</button>
        <button onclick="event.preventDefault(); document.querySelector('#addsecuritykey').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["securitykeyadded", "Se ha registrado correctamente la llave de seguridad."],
    ["securitykeydeleted", "Se ha eliminado correctamente la llave de seguridad."]
  ]);
  ?>

  <script src="js/common_webauthn.js"></script>
  <script src="js/securitykeys.js"></script>
</body>
</html>
