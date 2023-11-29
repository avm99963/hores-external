<?php
require_once("core.php");
security::checkType(security::ADMIN);

$mdHeaderRowBefore = visual::backBtn("settings.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .addincident {
    position: fixed;
    bottom: 16px;
    right: 16px;
    z-index: 1000;
  }

  @media (max-width: 655px) {
    .extra {
      display: none;
    }
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <button class="addincident mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Tipos de incidencias</h2>
          <?php
          $incidents = incidents::getTypes();
          if (count($incidents)) {
            ?>
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <thead>
                  <tr>
                    <?php
                    if ($conf["debug"]) {
                      ?>
                      <th class="extra">ID</th>
                      <?php
                    }
                    ?>
                    <th class="mdl-data-table__cell--non-numeric">Nombre</th>
                    <th class="mdl-data-table__cell--centered"><i id="tt_present" class="material-icons help">business</i></th>
                    <th class="mdl-data-table__cell--centered"><i id="tt_paid" class="material-icons help">euro_symbol</i></th>
                    <th class="mdl-data-table__cell--centered"><i id="tt_workerfill" class="material-icons help">face</i></th>
                    <th class="mdl-data-table__cell--centered"><i id="tt_notifies" class="material-icons help">email</i></th>
                    <th class="mdl-data-table__cell--centered"><i id="tt_autovalidates" class="material-icons help">verified_user</i></th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($incidents as $t) {
                    ?>
                    <tr<?=($t["hidden"] == 1 ? " class='mdl-color-text--grey-600'" : "")?>>
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <td class="extra"><?=(int)$t["id"]?></td>
                        <?php
                      }
                      ?>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($t["name"])?></td>
                      <td class="mdl-data-table__cell--centered"><?=($t["present"] == 1 ? visual::YES : "")?></td>
                      <td class="mdl-data-table__cell--centered"><?=($t["paid"] == 1 ? visual::YES : "")?></td>
                      <td class="mdl-data-table__cell--centered"><?=($t["workerfill"] == 1 ? visual::YES : "")?></td>
                      <td class="mdl-data-table__cell--centered"><?=($t["notifies"] == 1 ? visual::YES : "")?></td>
                      <td class="mdl-data-table__cell--centered"><?=($t["autovalidates"] == 1 ? visual::YES : "")?></td>
                      <td class='mdl-data-table__cell--non-numeric'><a href='dynamic/editincidenttype.php?id=<?=security::htmlsafe($t["id"])?>' data-dyndialog-href='dynamic/editincidenttype.php?id=<?=security::htmlsafe($t["id"])?>' title='Editar tipo de incidencia'><i class='material-icons icon'>edit</i></a></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
            visual::addTooltip("tt_present", "Indica si el trabajador está físicamente presente en el espacio de trabajo durante la incidencia (es la opción que indica si las horas cuentan como positivas o negativas).");
            visual::addTooltip("tt_paid", "Indica si el trabajador es remunerado las horas que dura la incidencia.");
            visual::addTooltip("tt_workerfill", "Indica si se permite que el trabajador pueda rellenar una incidencia de este tipo él mismo (con la posterior verificación por parte de un administrador).");
            visual::addTooltip("tt_notifies", "Indica si la introducción de una incidencia de este tipo notifica por correo electrónico a las personas especificadas en la categoría del trabajador.");
            visual::addTooltip("tt_autovalidates", "Indica si al introducir una incidencia de este tipo se autovalida sin necesidad de ser validada posteriormente por el trabajador.");
          } else {
            ?>
            <p>Todavía no hay definido ningún tipo de incidencia.</p>
            <p>Puedes añadir uno haciendo clic en el botón de la esquina inferior derecha de la página.</p>
            <?php
          }
          ?>

          <?php visual::printDebug("incidents::getTypes()", $incidents); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="addincident">
    <form action="doaddincidenttype.php" method="POST" autocomplete="off">
      <h4 class="mdl-dialog__title">Añade un tipo de incidencia</h4>
      <div class="mdl-dialog__content">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre del tipo de incidencia</label>
        </div>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="present">
            <input type="checkbox" id="present" name="present" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Presente <i class="material-icons help" id="add_present">help</i></span>
          </label>
          <div class="mdl-tooltip" for="add_present">Márquese si el trabajador está físicamente presente en el espacio de trabajo durante la incidencia.</div>
        </p>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="paid">
            <input type="checkbox" id="paid" name="paid" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Remunerada <i class="material-icons help" id="add_paid">help</i></span>
          </label>
          <div class="mdl-tooltip" for="add_paid">Márquese si el trabajador es remunerado las horas que dura la incidencia.</div>
        </p>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="workerfill">
            <input type="checkbox" id="workerfill" name="workerfill" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Puede autorrellenarse <i class="material-icons help" id="add_workerfill">help</i></span>
          </label>
        </p>
        <div class="mdl-tooltip" for="add_workerfill">Márquese si se permite que el trabajador pueda rellenar una incidencia de este tipo él mismo (con la posterior verificación por parte de un administrador).</div>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="notifies">
            <input type="checkbox" id="notifies" name="notifies" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Notifica <i class="material-icons help" id="add_notifies">help</i></span>
          </label>
          <div class="mdl-tooltip" for="add_notifies">Márquese si la introducción de una incidencia de este tipo se quiere que se notifique por correo electrónico a las personas especificadas en la categoría del trabajador.</div>
        </p>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="autovalidates">
            <input type="checkbox" id="autovalidates" name="autovalidates" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Se autovalida <i class="material-icons help" id="add_autovalidates">help</i></span>
          </label>
          <div class="mdl-tooltip" for="add_autovalidates">Márquese si al introducir una incidencia de este tipo se quiere que se autovalide sin necesidad de ser validada posteriormente por el trabajador.</div>
        </p>
        <p>
          <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="hidden">
            <input type="checkbox" id="hidden" name="hidden" value="1" class="mdl-switch__input">
            <span class="mdl-switch__label">Oculto</span>
          </label>
        </p>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
        <button onclick="event.preventDefault(); document.querySelector('#addincident').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["added", "Se ha añadido el tipo de incidencia correctamente."],
    ["modified", "Se ha modificado el tipo de incidencia correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>

  <script src="js/incidenttypes.js"></script>
</body>
</html>
