<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

$pending = incidents::numPending();
?>
<header class="mdl-layout__header mdl-layout__header--scroll<?=(visual::isMDColor() ? security::htmlsafe(" mdl-color--".$conf["backgroundColor"]) : "")?>"<?=(!visual::isMDColor() ? " style=\"background-color: ".security::htmlsafe($conf["backgroundColor"]).";\"" : "")?>>
  <div class="mdl-layout__header-row">
    <!-- Title -->
    <?php if (isset($mdHeaderRowBefore)) { echo $mdHeaderRowBefore; } ?>
    <span class="mdl-layout-title">Panel de control</span>
    <?php if (isset($mdHeaderRowMore)) { echo $mdHeaderRowMore; } ?>
  </div>
  <?php if (isset($mdHeaderMore)) { echo $mdHeaderMore; } ?>
</header>
<div class="mdl-layout__drawer">
  <span class="mdl-layout-title"><?=(!empty($conf["logo"] ?? "") ? "<img class=\"logo\" src=\"".security::htmlsafe($conf["logo"])."\">" : "")?> <?=security::htmlsafe($conf["appName"])?></span>
  <span class="subtitle mdl-color-text--grey-700"><?=security::htmlsafe(people::userData("name"))?></span>
  <nav class="mdl-navigation">
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="home.php"><i class="material-icons">dashboard</i> <span>Panel de Control</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="users.php"><i class="material-icons">group</i> <span>Personas</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="workers.php"><i class="material-icons">work</i> <span>Trabajadores</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="incidents.php?goTo=today"><i class="material-icons mdl-badge<?=($pending > 0 ? ' mdl-badge--overlap' : '')?>" <?=($pending > 0 ? 'data-badge="'.$pending.'"' : '')?>>assignment_late</i> <span>Incidencias</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="registry.php"><i class="material-icons">list</i> <span>Registro</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="export.php"><i class="material-icons">cloud_download</i> <span>Exportar registro</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="settings.php"><i class="material-icons">settings</i> <span>Configuración</span></a>
    <?php
    if ($conf["enableWorkerUI"]) {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="workerhome.php"><i class="material-icons">open_in_browser</i> <span>Vista de trabajador</span></a>
      <?php
    } elseif (secondFactor::isAvailable()) {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="security.php"><i class="material-icons">security</i> <span>Seguridad</span></a>
      <?php
    } else {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="changepassword.php"><i class="material-icons">account_circle</i> <span>Cambiar contraseña</span></a>
      <?php
    }
    ?>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" class="mdl-navigation__link" href="logout.php"><i class="material-icons">power_settings_new</i> <span>Cerrar sesión</span></a>
  </nav>
</div>
