<?php
class validationsView {
  public static function renderPendingValidations($userId) {
    $workers = workers::getPersonWorkers((int)$userId);
    $companies = companies::getAll();

    if ($workers === false || $companies === false) {
      return false;
    }
    ?>
    <h4>Incidencias</h4>
    <?php
    $iFlag = false;
    foreach ($workers as $w) {
      $incidents = incidents::getAll(false, 0, 0, (int)$w["id"], null, null, true);
      if ($incidents === false) continue;

      if (count($incidents)) {
        $iFlag = true;
        echo "<h5>".security::htmlsafe($w["companyname"])."</h5>";
        incidentsView::renderIncidents($incidents, $companies, false, false, true, true);
      }

      visual::printDebug("incidents::getAll(false, 0, 0, ".(int)$w["id"].", null, null, true)", $incidents);
    }

    if (!$iFlag) echo "<p>No hay incidencias pendientes para validar.</p>";
    ?>

    <h4>Registros de horario</h4>
    <?php
    $rFlag = false;
    foreach ($workers as $w) {
      $registry = registry::getWorkerRecords((int)$w["id"], false, false, false, true);
      if ($registry === false) continue;

      if (count($registry)) {
        $rFlag = true;
        echo "<h5>".security::htmlsafe($w["companyname"])."</h5>";
        registryView::renderRegistry($registry, $companies, false, false, true, true);
      }

      visual::printDebug("registry::getWorkerRecords(".(int)$w["id"].", false, false, false, true)", $registry);
    }

    if (!$rFlag) echo "<p>No hay registros pendientes para validar.</p>";

    if ($iFlag || $rFlag) {
      ?>
      <p style="margin-top: 16px;"><button id="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Validar</button></p>
      <?php
    }
  }

  public static function renderChallengeInstructions($method) {
    switch ($method) {
      case validations::METHOD_SIMPLE:
      ?>
      <form action="dovalidate.php" method="POST">
        <input type="hidden" name="incidents" value="<?=security::htmlsafe($_POST["incidents"] ?? "")?>">
        <input type="hidden" name="records" value="<?=security::htmlsafe($_POST["records"] ?? "")?>">
        <input type="hidden" name="method" value="<?=(int)validations::METHOD_SIMPLE?>">
        <p>Para completar la validación guardaremos tu <a href="https://help.gnome.org/users/gnome-help/stable/net-what-is-ip-address.html.es" target="_blank" rel="noopener noreferrer">dirección IP</a> y la fecha y hora actual. Para proceder, haz clic en el siguiente botón:</p>
        <p><button id="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent">Confirmar validación</button></p>
      </form>
      <?php
      break;

      default:
      echo "Undefined";
    }
  }

  public static function noPIIIpAddress($validation) {
    $ipAddress = $validation["attestation"]["ipAddress"] ?? "unknown";

    if (security::isAdminView() || $validation["method"] == validations::METHOD_SIMPLE || ($validation["method"] == validations::METHOD_AUTOVALIDATION && $validation["attestation"]["user"] == people::userData("id"))) return $ipAddress;

    return "oculta (solo visible para los administradores)";
  }

  public static function renderValidationInfo($validation) {
    ?>
    <li><b>Validada por el trabajador:</b></li>
    <ul>
      <li><b>Método de validación:</b> <?=security::htmlsafe(validations::$methodName[$validation["method"]])?></li>
      <li><b>Fecha de validación:</b> <?=security::htmlsafe(date("d/m/Y H:i", $validation["timestamp"]))?></li>
      <?php
      switch ($validation["method"]) {
        case validations::METHOD_SIMPLE:
        echo "<li><b>Dirección IP:</b> ".security::htmlsafe(self::noPIIIpAddress($validation))."</li>";
        break;

        case validations::METHOD_AUTOVALIDATION:
        echo "<li><b>Dirección IP:</b> ".security::htmlsafe(self::noPIIIpAddress($validation))."</li>";
        echo "<li><b>Persona que autovalida la incidencia:</b> ".security::htmlsafe(people::userData("name", $validation["attestation"]["user"]))."</li>";
        break;

        default:
      }
      ?>
    </ul>
    <?php
  }
}
