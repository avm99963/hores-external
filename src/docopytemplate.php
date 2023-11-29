<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["template", security::PARAM_ISINT],
  ["workers", security::PARAM_ISARRAY]
])) {
  security::go("workers.php?msg=unexpected");
}

$template = (int)$_POST["template"];
$active = ((isset($_POST["active"]) && $_POST["active"] == 1) ? 1 : 0);

$mdHeaderRowBefore = visual::backBtn("workers.php");
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
          <h2>Resultado de la copia de plantilla</h2>

          <?php
          foreach ($_POST["workers"] as $workerid) {
            $worker = workers::get($workerid);
            if ($worker === false) continue;

            $status = schedules::copyTemplate($template, $worker["id"], $active);
            $person = "&ldquo;".security::htmlsafe($worker["name"])."&rdquo; (".security::htmlsafe($worker["companyname"]).")";

            switch ($status) {
              case 0:
              echo "<p class='mdl-color-text--green'>Plantilla copiada correctamente a $person.";
              break;

              case 2:
              echo "<p class='mdl-color-text--orange'>El horario de la plantilla se solapa con uno de los horarios de $person, así que no se ha copiado al trabajador.";
              break;

              case 1:
              echo "<p class='mdl-color-text--red'>No se ha podido copiar la plantilla a $person porque la plantilla no existe.";
              break;

              case -1:
              echo "<p class='mdl-color-text--red'>Se ha empezado a copiar la plantilla a $person pero no se han podido copiar todos los horarios de cada día correctamente por algún error desconocido.";
              break;

              default:
              echo "<p class='mdl-color-text--red'>Ha ocurrido un error inesperado copiando la plantilla a $person.";
            }
            echo "</p>";
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
