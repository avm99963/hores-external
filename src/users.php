<?php
require_once("core.php");
security::checkType(security::ADMIN);

$mdHeaderRowMore = '<div class="mdl-layout-spacer"></div>
<div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable
            mdl-textfield--floating-label mdl-textfield--align-right">
  <label class="mdl-button mdl-js-button mdl-button--icon"
         for="usuario">
    <i class="material-icons">search</i>
  </label>
  <div class="mdl-textfield__expandable-holder">
    <input class="mdl-textfield__input" type="text" name="usuario"
           id="usuario">
  </div>
</div>';

listings::buildSelect("users.php");

$categories = categories::getAll();
$companies = companies::getAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  .adduser {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  .importcsv {
    position:fixed;
    bottom: 80px;
    right: 25px;
  }
  .filter {
    position:fixed;
    bottom: 126px;
    right: 25px;
  }
  .adduser, .importcsv, .filter {
    z-index: 1000;
  }

  @media (max-width: 655px) {
    .extra {
      display: none;
    }
  }

  /* Hide datable's search box */
  .dataTables_wrapper .mdl-grid:first-child {
    display: none;
  }
  .dt-table {
    padding: 0!important;
  }
  .dt-table .mdl-cell {
    margin: 0!important;
  }
  #usuario {
    position: relative;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <button class="adduser mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">person_add</i><span class="mdl-ripple"></span></button>
    <button class="importcsv mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">file_upload</i></button>
    <button class="filter mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">filter_list</i></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Personas</h2>
          <div class="overflow-wrapper overflow-wrapper--for-table">
            <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp datatable">
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
                  <th class="mdl-data-table__cell--non-numeric">Categoría</th>
                  <th class="mdl-data-table__cell--non-numeric extra">Tipo</th>
                  <th class="mdl-data-table__cell--centered">Baja</th>
                  <th class="mdl-data-table__cell--non-numeric"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $people = people::getAll($select);
                foreach ($people as $p) {
                  ?>
                  <tr>
                    <?php
                    if ($conf["debug"]) {
                      ?>
                      <td class="extra"><?=(int)$p["id"]?></td>
                      <?php
                    }
                    ?>
                    <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($p["name"])?></td>
                    <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(($p["categoryid"] == -1 ? "-" : $p["category"]))?></td>
                    <td class="mdl-data-table__cell--non-numeric extra"><?=security::htmlsafe(security::$types[$p["type"]])?></td>
                    <td class="mdl-data-table__cell--centered"><?=($p["baixa"] == 1 ? visual::YES : "")?></td>
                    <td class='mdl-data-table__cell--non-numeric'>
                      <a href='dynamic/user.php?id=<?=(int)$p['id']?>' data-dyndialog-href='dynamic/user.php?id=<?=(int)$p['id']?>' title='Ver información completa'><i class='material-icons icon'>open_in_new</i></a>
                      <?php if (security::isAllowed($p['type'])) { ?>
                        <a href='dynamic/edituser.php?id=<?=(int)$p['id']?>' data-dyndialog-href='dynamic/edituser.php?id=<?=(int)$p['id']?>' title='Editar persona'><i class='material-icons icon'>edit</i></a>
                      <?php } ?>
                      <a href='dynamic/companyuser.php?id=<?=(int)$p['id']?>' data-dyndialog-href='dynamic/companyuser.php?id=<?=(int)$p['id']?>' title='Ver y añadir empresas a la persona'><i class='material-icons icon'>work</i></a>
                      <?php if (count($p['companies'])) { ?>
                        <a href='workerschedule.php?id=<?=(int)$p['id']?>' title='Ver y gestionar los horarios de la persona'><i class='material-icons icon'>timelapse</i></a>
                        <a href='userincidents.php?id=<?=(int)$p['id']?>' title='Ver y gestionar las incidencias del trabajador'><i class='material-icons icon'>assignment_late</i></a>
                        <a href='userregistry.php?id=<?=(int)$p['id']?>' title='Ver y gestionar los registros del trabajador'><i class='material-icons icon'>list</i></a>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
            </table>
          </div>

          <?php visual::printDebug("people::getAll(\$select)", $people); ?>
          <?php visual::printDebug("\$select", $select); ?>
        </div>
      </div>
    </main>
  </div>

  <dialog class="mdl-dialog" id="adduser">
    <form action="doadduser.php" method="POST" autocomplete="off">
      <h4 class="mdl-dialog__title">Añade un trabajador</h4>
      <div class="mdl-dialog__content">
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="username" id="username" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="username">Nombre de usuario</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="name" id="name" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="name">Nombre</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="text" name="dni" id="dni" autocomplete="off">
          <label class="mdl-textfield__label" for="dni">DNI (opcional)</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="email" name="email" id="email" autocomplete="off">
          <label class="mdl-textfield__label" for="email">Correo electrónico (opcional)</label>
        </div>
        <br>
        <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
          <select name="category" id="category" class="mdlext-selectfield__select">
            <option value="-1"></option>
            <?php
            foreach ($categories as $id => $category) {
              echo '<option value="'.(int)$id.'">'.security::htmlsafe($category).'</option>';
            }
            ?>
          </select>
          <label for="category" class="mdlext-selectfield__label">Categoría (opcional)</label>
        </div>
        <br>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <input class="mdl-textfield__input" type="password" name="password" id="password" autocomplete="off" data-required>
          <label class="mdl-textfield__label" for="password">Contraseña</label>
        </div>
        <p><?=security::htmlsafe(security::$passwordHelperText)?></p>
        <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label" data-required>
          <select name="type" id="type" class="mdlext-selectfield__select">
            <?php
            foreach (security::$types as $i => $type) {
              echo '<option value="'.(int)$i.'"'.(security::isAllowed($i) ? "" : " disabled").'>'.security::htmlsafe($type).'</option>';
            }
            ?>
          </select>
          <label for="type" class="mdlext-selectfield__label">Tipo</label>
        </div>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
        <button onclick="event.preventDefault(); document.querySelector('#adduser').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <dialog class="mdl-dialog" id="importcsv">
    <form action="csvimport.php" method="POST" enctype="multipart/form-data">
      <h4 class="mdl-dialog__title">Importar CSV</h4>
      <div class="mdl-dialog__content">
        <p>Selecciona debajo el archivo CSV:</p>
        <p><input type="file" name="file" accept=".csv" required></p>
        <p>El formato de la cabecera debe ser: <code><?=security::htmlsafe(implode(";", csv::$fields))?></code></p>
        <p>En la columna <code>category</code>, introduce el ID de la categoría ya creada en el sistema (o <code>-1</code> si no quieres definir una categoría para esa persona), y en la columna <code>companies</code> introduce una lista separada por comas de los IDs de las empresas que quieres añadir a esa persona.</p>
      </div>
      <div class="mdl-dialog__actions">
        <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Importar</button>
        <button onclick="event.preventDefault(); document.querySelector('#importcsv').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
      </div>
    </form>
  </dialog>

  <?php listings::renderFilterDialog("users.php", $select); ?>

  <?php
  visual::smartSnackbar([
    ["added", "Se ha añadido la persona correctamente."],
    ["modified", "Se ha modificado la persona correctamente."],
    ["empty", "Faltan datos por introducir en el formulario o el correo electrónico es incorrecto."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["couldntupdatepassword", "Se ha actualizado la información pero no se ha podido actualizar la contraseña. Inténtelo de nuevo en unos segundos."],
    ["weakpassword", security::$passwordHelperText],
    ["disabledsecondfactor", "Se ha desactivado la verificación en dos pasos correctamente."]
  ]);
  ?>

  <script src="js/users.js"></script>
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="node_modules/datatables/media/js/jquery.dataTables.min.js"></script>
  <script src="lib/datatables/dataTables.material.min.js"></script>
</body>
</html>
