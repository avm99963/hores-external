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

security::changeActiveView(visual::VIEW_ADMIN);

$tips = array(
  "companies" => "<a href='companies.php'>Añade una o más empresas</a> antes de empezar. Esta utilidad te permite organizar el horario de trabajadores de varias empresas en la misma aplicación.",
  "categories" => "Opcionalmente, puedes <a href='categories.php'>crear categorías</a> para clasificar tus trabajadores, si te es conveniente.",
  "typesincidents" => "Para poder rellenar incidencias, es necesario que <a href='incidenttypes.php'>crees tipos de incidencias</a>. Estos sirven para determinar si una incidencia de ese tipo significa que se han trabajado horas extra o por si lo contrario el trabajador se auyenta del centro de trabajo, o si las horas de incidencia están remuneradas, etc.",
  "calendars" => "<a href='calendars.php'>Configura los calendarios</a> de días laborables, lectivos y festivos."
);

$checkEmptiness = ["companies", "categories", "typesincidents", "calendars"];
$isEmpty = [];

foreach ($checkEmptiness as $table) {
  if (db::numRows($table) == 0) $isEmpty[] = $table;
}

?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf["appName"]; ?></title>
  <?php visual::includeHead(); ?>
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
  .tip {
    border-radius: 4px;
    border: solid 1px #45b9dc;
    padding: 22px 16px;
  }

  .tip p:last-child {
    margin-bottom: 0;
  }

  li {
    margin-bottom: 0.5em;
  }

  li.done {
    color: #777;
  }

  li.done a {
    color: #555;
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
          <?php
          if (count($isEmpty)) {
            ?>
            <p>¡Hola <?=people::userData("name")?>! Antes de empezar a sacarle todo el jugo a la aplicación, nos gustaría sugerirte algunas acciones para empezar a configurarla:</p>
            <ol>
              <?php
              foreach ($checkEmptiness as $t) {
                $strike = !in_array($t, $isEmpty);
                echo "<li".($strike ? " class='done'" : "").">".$tips[$t]."</li>";
              }
              ?>
            </ol>
            <p>Después de realizar estas acciones, te sugerimos añadir trabajadores y configurar sus horarios desde la sección <a href="users.php">Trabajadores</a>. También te puede interesar acceder a la sección <a href="scheduletemplates.php">Plantillas de horarios</a> si vas a configurar el mismo horario múltiples veces a varias personas.</p>
            <?php
          } else {
            ?>
        		<p>¡Hola <?=people::userData("name")?>! Bienvenido a tu Panel de Control.</p>
            <?php
            if (!secondFactor::isEnabled()) {
              ?>
              <hr>
              <div class="tip mdl-color--blue-100 mdl-shadow--2dp">
                <p><b>Consejo:</b> debido a que eres un <?=security::htmlsafe(strtolower(security::$types[people::userData("type")]))?> y por lo tanto tu usuario tiene acceso ilimitado a todos los datos del aplicativo, para evitar que estos caigan en manos de un agente malicioso, te recomendamos que actives la <span class="highlighted">verificación en dos pasos</span>.</p>
                <p>La verificación en dos pasos consiste en la obligación de usar un segundo factor (como un código generado en tu móvil o tu huella dactilar) para iniciar sesión a parte de tu usuario y contraseña. <span class="highlighted">Mientras que una contraseña es relativamente fácil de obtener, para acceder al segundo factor el atacante debe tener acceso físico a un dispositivo tuyo, lo que disminuye el riesgo de sufrir un ataque.</span></p>
                <p>Puedes aprender más sobre la verificación en dos pasos en <a href="https://avm99963.github.io/hores-external/trabajadores/verificacion-en-dos-pasos/" target="_blank" rel="noopener noreferrer">este artículo de ayuda</a>.</p>
              </div>
              <?php
            }
          }
          ?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
