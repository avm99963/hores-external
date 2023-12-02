<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

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
