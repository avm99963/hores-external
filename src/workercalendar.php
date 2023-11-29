<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

$id = (int)people::userData("id");

$p = people::get($id);

if ($p === false) {
  security::go((security::isAdminView() ? "workers.php" : "workerhome.php"));
}

$cal = calendars::getCurrentCalendarByCategory($p["categoryid"]);
if ($cal === false) security::go("workerhome.php?msg=unexpected");

$mdHeaderRowBefore = visual::backBtn("workerschedule.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/calendar.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Calendario laboral</h2>

          <?php
          if ($cal === calendars::NO_CALENDAR_APPLICABLE) {
            echo "<p>Actualmente no tienes asignado ningún calendario laboral.</p>";
          } else {
            $current = new DateTime();
            $current->setTimestamp($cal["begins"]);
            $ends = new DateTime();
            $ends->setTimestamp($cal["ends"]);

            calendarsView::renderCalendar($current, $ends, function ($timestamp, $id, $dow, $dom, $extra) {
              return ($extra[$timestamp] == $id);
            }, true, $cal["details"]);
          }
          ?>

          <?php visual::printDebug("calendars::getCurrentCalendarByCategory(".(int)$p["categoryid"].")", $cal); ?>
        </div>
      </div>
    </main>
  </div>

  <script src="js/calendar.js"></script>

  <?php
  visual::smartSnackbar([
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."]
  ]);
  ?>
</body>
</html>
