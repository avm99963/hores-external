<?php
require_once("core.php");
security::checkType(security::ADMIN);

$mdHeaderRowBefore = visual::backBtn("powertools.php");

$gracePeriod = (int)($_GET["gracePeriod"] ?? validations::reminderGracePeriod());
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
          <h2>Validaciones pendientes</h2>
          <form action="pendingvalidations.php" method="GET">
            <p>Contar elementos que lleven más de <input type="text" name="gracePeriod" value="<?=(int)$gracePeriod?>" size="2"> días pendientes de validar. <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Generar tabla</button></p>
          </form>
          <?php
          $pending = validations::getPeopleWithPendingValidations($gracePeriod);
          usort($pending, function($a, $b) {
            $n =& $a["numPending"];
            $m =& $b["numPending"];
            return ($n > $m ? -1 : ($n < $m ? 1 : 0));
          });

          if (count($pending)) {
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
                    <th class="mdl-data-table__cell--non-numeric">Validaciones pendientes</th>
                    <th class="mdl-data-table__cell--non-numeric"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($pending as $p) {
                    ?>
                    <tr>
                      <?php
                      if ($conf["debug"]) {
                        ?>
                        <td class="extra"><?=(int)$p["person"]["id"]?></td>
                        <?php
                      }
                      ?>
                      <td class="mdl-data-table__cell--non-numeric"><?=security::htmlsafe($p["person"]["name"])?></td>
                      <td class="mdl-data-table__cell--non-numeric"><?=(int)$p["numPending"]?></td>
                      <td class='mdl-data-table__cell--non-numeric'>
                        <a href='userincidents.php?id=<?=(int)$p["person"]["id"]?>' title='Ver y gestionar las incidencias del trabajador'><i class='material-icons icon'>assignment_late</i></a>
                        <a href='userregistry.php?id=<?=(int)$p["person"]["id"]?>' title='Ver y gestionar los registros del trabajador'><i class='material-icons icon'>list</i></a>
                      </td>
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
            <p>Todavía no existe ninguna persona.</p>
            <?php
          }
          ?>

          <?php visual::printDebug("validations::getPeopleWithPendingValidations(".$gracePeriod.")", $pending); ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
