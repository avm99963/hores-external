<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);

$pending = validations::numPending();
?>
<header class="mdl-layout__header mdl-layout__header--scroll<?=(visual::isMDColor() ? security::htmlsafe(" mdl-color--".$conf["backgroundColor"]) : "")?>"<?=(!visual::isMDColor() ? "style=\"background-color: ".security::htmlsafe($conf["backgroundColor"]).";\"" : "")?>>
  <div class="mdl-layout__header-row">
    <!-- Title -->
    <?php if (isset($mdHeaderRowBefore)) { echo $mdHeaderRowBefore; } ?>
    <span class="mdl-layout-title"><?=(security::isAllowed(security::ADMIN) ? "Vista de trabajador" : "Panel de control")?></span>
    <?php if (isset($mdHeaderRowMore)) { echo $mdHeaderRowMore; } ?>
  </div>
  <?php if (isset($mdHeaderMore)) { echo $mdHeaderMore; } ?>
</header>
<div class="mdl-layout__drawer">
  <span class="mdl-layout-title"><?=(!empty($conf["logo"] ?? "") ? "<img class=\"logo\" src=\"".security::htmlsafe($conf["logo"])."\">" : "")?> <?=security::htmlsafe($conf["appName"])?></span>
  <span class="subtitle mdl-color-text--grey-700"><?=security::htmlsafe(people::userData("name"))?></span>
  <nav class="mdl-navigation">
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="workerhome.php"><i class="material-icons">dashboard</i> <span>Panel de Control</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="workerschedule.php"><i class="material-icons">timelapse</i> <span>Horario actual</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="userincidents.php?id=<?=(int)$_SESSION["id"]?>"><i class="material-icons mdl-badge">assignment_late</i> <span>Incidencias</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="userregistry.php?id=<?=(int)$_SESSION["id"]?>"><i class="material-icons mdl-badge">list</i> <span>Registro</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="validations.php"><i class="material-icons mdl-badge<?=($pending > 0 ? ' mdl-badge--overlap' : '')?>" <?=($pending > 0 ? 'data-badge="'.$pending.'"' : '')?>>verified_user</i> <span>Validaciones</span></a>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="export4worker.php?id=<?=(int)$_SESSION["id"]?>"><i class="material-icons mdl-badge">cloud_download</i> <span>Exportar registro</span></a>
    <?php
    if (secondFactor::isAvailable()) {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="security.php"><i class="material-icons">security</i> <span>Seguridad</span></a>
      <?php
    } else {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="changepassword.php"><i class="material-icons">account_circle</i> <span>Cambiar contraseña</span></a>
      <?php
    }

    if (security::isAllowed(security::ADMIN)) {
      ?>
      <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" href="home.php"><i class="material-icons">open_in_browser</i> <span>Vista de administrador</span></a>
      <?php
    }
    ?>
    <a class="mdl-navigation__link mdl-js-button mdl-js-ripple-effect" class="mdl-navigation__link" href="logout.php"><i class="material-icons">power_settings_new</i> <span>Cerrar sesión</span></a>
  </nav>
</div>
