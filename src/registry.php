<?php
require_once("core.php");
security::checkType(security::ADMIN);

$companies = companies::getAll();
$showInvalidated = (isset($_GET["showinvalidated"]) && $_GET["showinvalidated"] == 1);
$numRows = registry::numRows($showInvalidated);
$page = (isset($_GET["page"]) ? (int)$_GET["page"] - 1 : null);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/incidents.css">

  <style>
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
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <form action="registry.php" method="GET" id="show-invalidated-form" class="actions" style="padding-right: 32px;">
            <input type="hidden" name="page" value="<?=(int)($_GET["page"] ?? 1)?>">
            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="showinvalidated">
              <input type="checkbox" id="showinvalidated" name="showinvalidated" value="1" class="mdl-switch__input"<?=($showInvalidated ? " checked" : "")?>>
              <span class="mdl-switch__label mdl-color-text--grey-700">Mostrar registros invalidados</span>
            </label>
          </form>

          <h2>Registro</h2>

          <p>Total: <?=(int)$numRows?> registros</p>

          <?php
          $registry = registry::getRecords(false, false, false, $showInvalidated, true, true, $page);
          if (count($registry)) {
            registryView::renderRegistry($registry, $companies);
          } else {
            echo "El registro está vacío.";
          }
          ?>

          <?php
          visual::renderPagination($numRows, "registry.php?".($showInvalidated ? "showinvalidated=1" : ""), incidents::PAGINATION_LIMIT, false, true);
          visual::printDebug("registry::getRecords(false, false, false, ".($showInvalidated ? "true" : "false").", true, true, ".(int)$page.")", $registry);
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["invalidated", "Se ha invalidado el registro correctamente."]
  ]);
  ?>

  <script src="js/incidentsgeneric.js"></script>
  <script src="js/registry.js"></script>
</body>
</html>
