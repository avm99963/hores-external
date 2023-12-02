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
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

$isAdmin = security::isAllowed(security::ADMIN);

$mainURL = (security::isAdminView() ? "workers.php" : "workerhome.php");

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go($mainURL);
}

$id = (int)$_GET["id"];

if (!$isAdmin && people::userData("id") != $id) {
  security::notFound();
}

$p = people::get($id);

if ($p === false) {
  security::go($mainURL);
}

$workers = workers::getPersonWorkers((int)$p["id"]);
$companies = companies::getAll();

if ($workers === false || $companies === false) {
  security::go($mainURL."?msg=unexpected");
}

$numRows = registry::numRowsUser($p["id"]);

if (security::isAdminView()) $mdHeaderRowBefore = visual::backBtn("workers.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/incidents.css">
  <style>
  @media (max-width: 400px) {
    #exportbtn {
      display: none;
    }
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php
    visual::includeNav();
    ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php
          if (!security::isAdminView()) {
            ?>
            <div class="actions">
              <a href="export4worker.php?id=<?=(int)$_SESSION["id"]?>" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect" id="exportbtn"><i class="material-icons">cloud_download</i> Exportar registro e incidencias</a>
              <?php helpView::renderHelpButton(help::PLACE_REGISTRY_PAGE); ?>
            </div>
            <?php
          }
          ?>

          <?php
          $title = "Registro".(security::isAdminView() ? " de &ldquo;".security::htmlsafe($p["name"])."&rdquo;" : "");
          ?>
          <h2><?=$title?></h2>

          <p>Total: <?=(int)$numRows?> registros</p>

          <?php
          $page = (isset($_GET["page"]) ? (int)$_GET["page"] - 1 : null);

          $registry = registry::getRecords($p["id"], false, false, true, true, true, $page, registry::REGISTRY_PAGINATION_LIMIT, true);
          if (count($registry)) {
            registryView::renderRegistry($registry, $companies, false, true, !security::isAdminView());
          }
          ?>

          <?php
          visual::renderPagination($numRows, "userregistry.php?id=".(int)$p["id"], incidents::PAGINATION_LIMIT, false, true);
          visual::printDebug("registry::getRecords(".(int)$p["id"].", false, false, true, true, true, ".(int)$page.", registry::REGISTRY_PAGINATION_LIMIT, true)", $registry);
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::renderTooltips();

  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>

  <script src="js/userincidents.js"></script>
  <script src="js/incidentsgeneric.js"></script>
</body>
</html>
