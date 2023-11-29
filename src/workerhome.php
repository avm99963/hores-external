<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

security::changeActiveView(visual::VIEW_WORKER);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">

  <style>
  a {
    color: blue;
  }
  </style>
</head>
<?php visual::printBodyTag(); ?>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
    <?php visual::includeNav(); ?>
    <main class="mdl-layout__content">
      <div class="page-content">
        <div class="main mdl-shadow--4dp">
          <h2>Bienvenido</h2>
        	<p>¡Hola <?=people::userData("name")?>! Bienvenido a tu Panel de Control.</p>
          <p>Estas son las diferentes secciones que encontrarás en el aplicativo:</p>
          <ul>
            <li><b><a href="workerschedule.php">Horario actual</a>:</b> aquí puedes <span class="highlighted">ver tu horario actual</span>, tu <span class="highlighted">calendario laboral</span> y un <span class="highlighted">histórico de todos los horarios</span> que tienes asignados.</li>
            <li><b><a href="userincidents.php?id=<?=(int)$_SESSION["id"]?>">Incidencias</a>:</b> aquí puedes ver las <span class="highlighted">incidencias que tienes asignadas</span> e <span class="highlighted">introducir una nueva incidencia</span>.</li>
            <li><b><a href="userregistry.php?id=<?=(int)$_SESSION["id"]?>">Registro</a>:</b> aquí se encuentran los <span class="highlighted">registros de tu horario diario</span>. No tiene en cuenta las incidencias que has creado porque es únicamente el horario base de tu jornada laboral.</li>
            <li><b><a href="validations.php?>">Validaciones</a>:</b> aquí puedes <span class="highlighted">validar tus incidencias y registros de tu horario diario</span> que todavía no hayas validado.</li>
            <li><b><a href="export4worker.php?id=<?=(int)$_SESSION["id"]?>">Exportar registro</a>:</b> aquí puedes <span class="highlighted">descargar tu registro incluyendo incidencias como PDF</span>.</li>
          </ul>
        </div>
      </div>
    </main>
  </div>

  <?php
  visual::smartSnackbar([
    ["passwordchanged", "Se ha cambiado la contraseña correctamente."]
  ]);
  ?>
</body>
</html>
