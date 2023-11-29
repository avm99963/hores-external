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
  td .material-icons {
    vertical-align: middle;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Logs</h2>
          <?php
          $page = (isset($_GET["page"]) ? (int)$_GET["page"] - 1 : null);

          $logs = registry::getLogs($page);
          if (count($logs)) {
            ?>
            <div class="overflow-wrapper overflow-wrapper--for-table">
              <table class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
                <thead>
                  <tr>
                    <th class="extra">ID</th>
                    <th class="mdl-data-table__cell--non-numeric">Hora de ejecución</th>
                    <th class="mdl-data-table__cell--non-numeric">Día registrado</th>
                    <th class="mdl-data-table__cell--non-numeric">Ejecutado por</th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $tooltips = "";
                  foreach ($logs as $l) {
                    ?>
                    <tr>
                      <td class="extra"><?=(int)$l["id"]?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(date("d/m/Y H:i", $l["realtime"]))?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe(date("d/m/Y", $l["day"]))?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=($l["executedby"] == -1 ? "<span style='font-family: monospace;'>cron</span>" : security::htmlsafe(people::userData("name", $l["executedby"])))?></td>
                      <td class="mdl-data-table__cell--non-numeric">
                        <?php
                        if ($l["warningpos"] > 0) {
                          $tooltips .= '<div class="mdl-tooltip" for="warning_'.(int)$l["id"].'">El log contiene mensajes de advertencia</div>';
                          ?>
                          <i class="material-icons mdl-color-text--orange help" id="warning_<?=(int)$l["id"]?>">warning</i>
                          <?php
                        }

                        if ($l["errorpos"] > 0) {
                          $tooltips .= '<div class="mdl-tooltip" for="error_'.(int)$l["id"].'">El log contiene mensajes de error</div>';
                          ?>
                          <i class="material-icons mdl-color-text--red help" id="error_<?=(int)$l["id"]?>">error</i>
                          <?php
                        }

                        if ($l["fatalerrorpos"] > 0) {
                          $tooltips .= '<div class="mdl-tooltip" for="fatalerror_'.(int)$l["id"].'">El log contiene errores fatales</div>';
                          ?>
                          <i class="material-icons mdl-color-text--red help-900" id="fatalerror_<?=(int)$l["id"]?>">error</i>
                          <?php
                        }
                        ?>
                      </td>
                      <td class="mdl-data-table__cell--non-numeric">
                        <a href="dynamic/log.php?id=<?=(int)$l["id"]?>" data-dyndialog-href="dynamic/log.php?id=<?=(int)$l["id"]?>" title="Ver el log"><i class='material-icons icon'>notes</i></a>
                      </td>
                    </tr>
                    <?php
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
            echo $tooltips;
          } else {
            ?>
            <p>No existe ningún log todavía.</p>
            <?php
          }
          ?>

          <?php
          $numLogs = db::numRows("logs");
          visual::renderPagination($numLogs, "logs.php", registry::LOGS_PAGINATION_LIMIT);
          visual::printDebug("registry::getLogs(".(int)$page.")", $logs);
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
