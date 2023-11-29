<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

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
          <h2>Exportar base de datos</h2>

          <form action="dobackupdb.php" method="POST">
            <input type="hidden" name="format" value="<?=(int)db::EXPORT_DB_FORMAT_SQL?>">
            <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Exportar base de datos</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
