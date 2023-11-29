<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!isset($_FILES["file"]) || $_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE) {
  security::go("users.php?msg=empty");
}

if ($_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
  security::go("users.php?msg=unexpected");
}
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
          <h2>Resultado de la importación de usuarios</h2>

          <?php
          $workers = csv::csv2array($_FILES["file"]["tmp_name"]);

          if ($workers === false) {
            echo "<p class='mdl-color-text--red'>El formato del documento no es correcto (la cabecera debería ser: <code>".security::htmlsafe(implode(";", csv::$fields))."</code>).</p>";
          } else {
            foreach ($workers as $worker) {
              $username = explode("@", $worker["email"])[0];
              $passwordHash = password_hash($worker["dni"], PASSWORD_DEFAULT);

              $status = people::add($username, $worker["name"], $worker["dni"], $worker["email"], $worker["category"], $passwordHash, security::WORKER, 0);

              echo "<p class='mdl-color-text--".($status ? "green" : "red")."'>&ldquo;".security::htmlsafe($worker["name"])."&rdquo; ".($status ? "se ha importado correctamente" : " no se ha podido importar correctamente").".</p>";

              if ($status) {
                $personid = mysqli_insert_id($con);

                $companies = explode(",", $worker["companies"]);

                foreach ($companies as $company) {
                  $status2 = people::addToCompany($personid, $company);

                  echo "<p class='mdl-color-text--".($status2 ? "green" : "red")."'>".($status2 ? "Se ha" : "No se ha")." podido añadir &ldquo;".security::htmlsafe($worker["name"])."&rdquo; a la empresa con número de identificación ".(int)$company.".</p>";
                }
              }
            }
          }
          ?>

          <a href="users.php" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Ir al listado de personas</a>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
