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

incidentsView::handleIncidentShortcuts();

incidentsView::buildSelect();

$numRows = incidents::numRows($select);
$companies = companies::getAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/incidents.css">

  <style>
  .addincident, .addrecurringincident, .filter {
    z-index: 1000;
  }

  .addincident {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }

  .addrecurringincident {
    position: fixed;
    bottom: 80px;
    right: 25px;
  }

  .filter {
    position: fixed;
    bottom: 126px;
    right: 25px;
  }

  .helper-bellow-incidents-list {
    text-align: center;
    font-size: 13px;
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
    <button class="addrecurringincident mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">repeat</i><span class="mdl-ripple"></span></button>
    <button class="filter mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">filter_list</i></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Incidencias</h2>

          <?php
          if ($select["showPendingQueue"]) {
            $numRowsPending = incidents::numPending();
            ?>
            <h4>Cola de revisión (<?=$numRowsPending?>)<?=($select["isAnyEnabled"] ? ' <i id="tt_filtersarenotapplied" class="material-icons help">info</i>' : '')?></h4>
            <?php
            if ($select["isAnyEnabled"]) {
              visual::addTooltip("tt_filtersarenotapplied", "Los filtros no se han aplicado a la cola de revisión.");
            }

            $incidentsPending = incidents::getAll(true);
            if (count($incidentsPending)) {
              incidentsView::renderIncidents($incidentsPending, $companies);
            } else {
              echo "Todas las incidencias ya han sido revisadas.";
            }
          }


          $page = ($select["showResultsPaginated"] ? (isset($_GET["page"]) ? (int)$_GET["page"] - 1 : null) : 0);

          if ($select["showResultsPaginated"]) {
            $limit = (int)($_GET["limit"] ?? incidents::PAGINATION_LIMIT);
            if ($limit <= 0 || $limit > 100) $limit = incidents::PAGINATION_LIMIT;
          } else $limit = 0;

          $incidents = incidents::getAll(null, $page, $limit, "ALL", null, null, false, $select);

          if (!$select["showResultsPaginated"]) $numRows = count($incidents);
          ?>

          <h4><?=($select["isAnyEnabled"] ? "Incidencias filtradas" : "Todas las incidencias")?> (<?=(int)$numRows?>)</h4>

          <?php
          if (count($incidents)) {
            incidentsView::renderIncidents($incidents, $companies, false, true, false, false, true);
            /*if (!$select["showResultsPaginated"]) {
              echo "<p class=\"helper-bellow-incidents-list mdl-color-text--grey-600\">Se está truncando la lista de incidencias a un máximo de  resultados, así que es posible que algunas incidencias no se muestren en la lista.<br>Puedes encontrar más incidencias cambiando el selector de fecha final de la sección de filtros a la fecha de la última incidencia de la lista.</p>";
            }*/
          } else if ($select["isAnyEnabled"]) {
            echo "No se ha encontrado ninguna incidencia con los filtros seleccionados.";
          } else {
            echo "Todavía no existe ninguna incidencia. Puedes añadir una haciendo clic en el botón de la esquina inferior derecha.";
          }
          ?>

          <?php
          if ($select["showResultsPaginated"]) visual::renderPagination($numRows, $select["pageUrl"], $limit, ($limit == incidents::PAGINATION_LIMIT ? false : true), $select["pageUrlHasParameters"], [
            "elementName" => "incidencias",
            "options" => incidentsView::$limitOptions
          ], incidents::todayPage($limit));
          if ($select["showPendingQueue"]) visual::printDebug("incidents::getAll(true)", $incidentsPending);
          visual::printDebug("incidents::getAll(null, ".(int)$page.", ".(int)$limit.", \"ALL\", null, null, false, \$select)", $incidents);
          visual::printDebug("\$select", $select);
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  $workers = people::getAll(false, true);
  incidentsView::renderIncidentForm($workers, function(&$worker) {
    return (int)$worker["workerid"];
  }, function (&$worker, &$companies) {
    return $worker["name"].' ('.$companies[$worker["companyid"]].')';
  }, $companies, false, false, "incidents.php");

  incidentsView::renderIncidentForm($workers, function(&$worker) {
    return (int)$worker["workerid"];
  }, function (&$worker, &$companies) {
    return $worker["name"].' ('.$companies[$worker["companyid"]].')';
  }, $companies, false, true, "incidents.php");

  if (isset($_GET["openRecurringFormWorker"])) {
    $openWorker = (int)$_GET["openRecurringFormWorker"];
    ?>
    <script>
    document.getElementById("recurringworker").value = "<?=security::htmlsafe($openWorker)?>";
    document.getElementById("addrecurringincident").showModal();
    </script>
    <?php
  }

  incidentsView::renderFilterDialog($select);

  visual::renderTooltips();
  ?>

  <div class="mdl-snackbar mdl-js-snackbar">
    <div class="mdl-snackbar__text"></div>
    <button type="button" class="mdl-snackbar__action"></button>
  </div>

  <?php
  visual::smartSnackbar(incidentsView::$incidentsMsgs, 10000, false);
  ?>

  <script>
  var _showResultsPaginated = <?=($select["showResultsPaginated"] ? "true" : "false")?>;
  var _limit = <?=(int)$limit?>;
  var _page = <?=(int)$page?>;
  </script>
  <script src="js/incidents.js"></script>
  <script src="js/incidentsgeneric.js"></script>
</body>
</html>
