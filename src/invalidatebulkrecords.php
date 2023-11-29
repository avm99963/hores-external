<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

$companies = companies::getAll();

$mdHeaderRowBefore = visual::backBtn("powertools.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Invalidar registros en masa</h2>
          <p>Selecciona los trabajadores de los cuales quieres invalidar sus registros, y el periodo de tiempo aplicable:</p>
          <form action="doinvalidatebulkrecords.php" method="POST">
            <h5>Periodo de tiempo</h5>
            <p>Del <input type="date" name="begins" required> al <input type="date" name="ends" required></p>

            <h5>Personas</h5>

            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-data-table--selectable mdl-shadow--2dp">
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
                    <th class="mdl-data-table__cell--non-numeric">Empresa</th>
                    <th class="mdl-data-table__cell--non-numeric extra">Categoría</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $workers = people::getAll(false, true);
                  foreach ($workers as $w) {
                    ?>
                    <tr data-worker-id="<?=(int)$w["workerid"]?>">
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <td class="extra"><?=(int)$w["workerid"]?></td>
                        <?php
                      }
                      ?>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($w["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($companies[$w["companyid"]])?></td>
                      <td class="mdl-data-table__cell--non-numeric extra"><?=security::htmlsafe($w["category"])?></td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <br>
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Invalidar</button>
          </form>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["inverted", "La fecha de inicio debe ser igual o anterior a la de fin."],
    ["success", "Se han invalidado todos los registros correspondientes correcamente."],
    ["partialortotalfailure", "Varios (o todos) los registros no se han podido invalidar correctamente."]
  ]);
  ?>
  <script src="js/invalidatebulkrecords.js"></script>
</body>
</html>
