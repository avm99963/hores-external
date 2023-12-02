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

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT]
])) {
  security::go((security::isAdminView() ? "workers.php" : "workerhome.php"));
}

$id = (int)$_GET["id"];

if (!$isAdmin && people::userData("id") != $id) {
  security::notFound();
}

$p = people::get($id);

if ($p === false) {
  security::go((security::isAdminView() ? "workers.php" : "workerhome.php"));
}

$workers = workers::getPersonWorkers((int)$p["id"]);
$companies = companies::getAll();

if ($workers === false || $companies === false) {
  security::go((security::isAdminView() ? "workers.php" : "workerhome.php")."?msg=unexpected");
}

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
  .addincident, .addrecurringincident {
    z-index: 1000;
  }

  .addincident {
    position: fixed;
    bottom: 16px;
    right: 16px;
  }
  <?php
  if (security::isAdminView()) {
    ?>
    .addrecurringincident {
      position: fixed;
      bottom: 80px;
      right: 25px;
    }
    <?php
  }
  ?>
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php
    visual::includeNav();
    if (security::isAdminView()) {
      ?>
      <button class="addrecurringincident mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab mdl-color--grey-200"><i class="material-icons">repeat</i><span class="mdl-ripple"></span></button>
      <?php
    }
    ?>
    <button class="addincident mdl-button md-js-button mdl-button--fab mdl-js-ripple-effect mdl-button--accent"><i class="material-icons">add</i><span class="mdl-ripple"></span></button>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <?php
          $title = "Incidencias".(security::isAdminView() ? " de &ldquo;".security::htmlsafe($p["name"])."&rdquo;" : "");
          ?>
          <h2><?=$title?></h2>

          <?php
          if (count($workers)) {
            foreach ($workers as $w) {
              $incidents = incidents::getAll(false, 0, 0, (int)$w["id"]);

              echo "<h4>".security::htmlsafe($companies[$w["company"]]).($w["hidden"] == 1 ? " <span class='mdl-color-text--grey-600'>(dada de baja)</span>" : "")."</h4>";

              if (count($incidents)) {
                incidentsView::renderIncidents($incidents, $companies, true, false, !security::isAdminView(), false, false, "userincidents.php?id=".(int)$p["id"]);
              } else {
                echo "<p>Todavía no existe ninguna incidencia ".(security::isAdminView() ? "para este trabajador " : "")."en esta empresa.</p>";
              }

              visual::printDebug("incidents::getAll(false, 0, 0, ".(int)$w["id"].")", $incidents);
            }
          } else {
            echo "<p>".(security::isAdminView() ? "Antes de poder visualizar y añadir incidencias a este trabajador deberías darlo de alta en alguna empresa." : "No puedes visualizar ni añadir incidencias porque todavía no se te ha asignado ninguna empresa.")."</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>

  <?php
  incidentsView::renderIncidentForm($workers, function(&$worker) {
    return (int)$worker["id"];
  }, function (&$worker, &$companies) {
    return $worker["name"].' ('.$companies[$worker["company"]].')';
  }, $companies, !$isAdmin, false, "userincidents.php?id=".(int)$p["id"]);

  if (security::isAdminView()) {
    incidentsView::renderIncidentForm($workers, function(&$worker) {
      return (int)$worker["id"];
    }, function (&$worker, &$companies) {
      return $worker["name"].' ('.$companies[$worker["company"]].')';
    }, $companies, false, true, "userincidents.php?id=".(int)$p["id"]);
    ?>
    <script>
    window.addEventListener("load", function() {
      document.querySelector(".addrecurringincident").addEventListener("click", function() {
        document.querySelector("#addrecurringincident").showModal();
        /* Or dialog.show(); to show the dialog without a backdrop. */
      });
    });
    </script>
    <?php
  }

  visual::renderTooltips();

  visual::smartSnackbar(incidentsView::$incidentsMsgs);
  ?>

  <script src="js/userincidents.js"></script>
  <script src="js/incidentsgeneric.js"></script>
</body>
</html>
