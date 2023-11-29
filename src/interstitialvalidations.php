<?php
require_once("core.php");
security::checkType(security::WORKER);

$id = people::userData("id");

$allowedMethods = validations::getAllowedMethods();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/incidents.css">
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Validación</h2>
          <div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
            <div class="mdl-tabs__tab-bar">
              <?php
              foreach ($allowedMethods as $method) {
                echo '<a href="#method'.security::htmlsafe(validations::$methodCodename[$method]).'" class="mdl-tabs__tab'.($method == $conf["validation"]["defaultMethod"] ? " is-active" : "").'">'.security::htmlsafe(validations::$methodName[$method]).'</a>';
              }
              ?>
            </div>
            <?php
            foreach ($allowedMethods as $method) {
              echo '<div class="mdl-tabs__panel'.($method == $conf["validation"]["defaultMethod"] ? " is-active" : "").'" id="method'.security::htmlsafe(validations::$methodCodename[$method]).'">';
              validationsView::renderChallengeInstructions($method);
              echo '</a>';
            }
            ?>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
