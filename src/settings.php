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

$conf_redacted = $conf;
foreach ($conf_redacted["db"] as &$el) {
  $el = "*CENSURADO*";
}
$conf_redacted["mail"]["password"] = "*CENSURADO*";
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
          <h2>Configuración</h2>
          <a class="clicky-container" href="companies.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Empresas</span><br>
                <span class="description">Configura las diferentes empresas de la aplicación.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="categories.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Categorías de trabajadores</span><br>
                <span class="description">Configura las categorías en las que se pueden clasificar los trabajadores.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="incidenttypes.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Tipos de incidencias</span><br>
                <span class="description">Configura los diferentes motivos que se pueden seleccionar al crear una incidencia.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="calendars.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Calendarios</span><br>
                <span class="description">Configura los días festivos, lectivos y laborables del año.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <a class="clicky-container" href="logs.php">
            <div class="clicky mdl-js-ripple-effect">
              <div class="text">
                <span class="title">Logs</span><br>
                <span class="description">Ver los logs del programa que registra los horarios diariamente.</span>
              </div>
              <div class="mdl-ripple"></div>
            </div>
          </a>
          <?php
          if (security::isAllowed(security::HYPERADMIN)) {
            ?>
            <a class="clicky-container" href="help.php">
              <div class="clicky mdl-js-ripple-effect">
                <div class="text">
                  <span class="title">Recursos de ayuda</span><br>
                  <span class="description">Configura los enlaces de ayuda que se ofrecen a los trabajadores en el aplicativo.</span>
                </div>
                <div class="mdl-ripple"></div>
              </div>
            </a>
            <a class="clicky-container" href="powertools.php">
              <div class="clicky mdl-js-ripple-effect">
                <div class="text">
                  <span class="title">Herramientas avanzadas</span><br>
                  <span class="description">Ver herramientas para hiperadministradores del aplicativo.</span>
                </div>
                <div class="mdl-ripple"></div>
              </div>
            </a>
            <?php
          }

          if ($conf["debug"]) {
            ?>
            <details class="debug margintop">
              <summary>Ajustes establecidos en el fichero <code>config.php</code>:</summary>
              <pre><?=visual::debugJson($conf_redacted); ?></pre>
            </details>
            <?php
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
