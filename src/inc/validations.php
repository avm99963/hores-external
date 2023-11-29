<?php
class validations {
  const DEFAULT_REMINDER_GRACE_PERIOD = 3; // When sending email notifications about pending elements to verify,
                                   // only elements effective until the current day minus the grace period
                                   // will be checked for validation. (value is given in days)

  const METHOD_SIMPLE = 0;
  const METHOD_AUTOVALIDATION = 1;

  public static $methodCodename = [
    0 => "simple",
    1 => "autovalidation"
  ];

  public static $methodName = [
    0 => "Validación por dirección IP",
    1 => "Validación automática"
  ];

  public static $methods = [self::METHOD_SIMPLE, self::METHOD_AUTOVALIDATION];
  public static $manualMethods = [self::METHOD_SIMPLE];

  public static function reminderGracePeriod() {
    return $conf["validation"]["gracePeriod"] ?? self::DEFAULT_REMINDER_GRACE_PERIOD;
  }

  public static function numPending($userId = "ME", $gracePeriod = 0) {
    global $con;

    if ($userId === "ME") $userId = people::userData("id");
    $suser = (int)$userId;

    $query = mysqli_query($con, "SELECT COUNT(*) count FROM incidents i INNER JOIN workers w ON i.worker = w.id WHERE w.person = ".$suser." AND ".incidents::$workerPendingWhere." AND ".incidents::$activeWhere.($gracePeriod === false ? "" : " AND (i.day + i.begins) < ".(int)(time() - (int)$gracePeriod*(24*60*60))));

    if (!mysqli_num_rows($query)) return "?";

    $row = mysqli_fetch_assoc($query);

    $count = (int)(isset($row["count"]) ? $row["count"] : 0);

    $query2 = mysqli_query($con, "SELECT COUNT(*) count FROM records r INNER JOIN workers w ON r.worker = w.id WHERE w.person = ".$suser." AND ".registry::$notInvalidatedWhere." AND ".registry::$workerPendingWhere.($gracePeriod === false ? "" : " AND r.day < ".(int)(time() - (int)$gracePeriod*(24*60*60))));

    if (!mysqli_num_rows($query2)) return "?";

    $row2 = mysqli_fetch_assoc($query2);

    return $count + (int)(isset($row2["count"]) ? $row2["count"] : 0);
  }

  public static function getAllowedMethods() {
    global $conf;

    $allowedMethods = [];
    foreach (self::$manualMethods as $method) {
      if (in_array($method, $conf["validation"]["allowedMethods"])) $allowedMethods[] = $method;
    }

    return $allowedMethods;
  }

  private static function string2array($string) {
    if (!is_string($string) || empty($string)) return [];
    $explode = explode(",", $string);

    $array = [];
    foreach ($explode as $el) {
      $array[] = (int)$el;
    }

    return $array;
  }

  private static function createSimpleAttestation($method, $user) {
    $attestation = [];
    if (!isset($_SERVER["REMOTE_ADDR"])) return false;
    $attestation["ipAddress"] = (string)$_SERVER["REMOTE_ADDR"];
    if ($method === self::METHOD_AUTOVALIDATION) $attestation["user"] = (int)$user;
    return $attestation;
  }

  private static function createIncidentAttestation(&$incident, $method, $user) {
    switch ($method) {
      case self::METHOD_SIMPLE:
      case self::METHOD_AUTOVALIDATION:
      return self::createSimpleAttestation($method, $user);
      break;

      default:
      return false;
    }
  }

  private static function createRecordAttestation(&$record, $method, $user) {
    switch ($method) {
      case self::METHOD_SIMPLE:
      case self::METHOD_AUTOVALIDATION:
      return self::createSimpleAttestation($method, $user);
      break;

      default:
      return false;
    }
  }

  private static function createValidation($method, $attestation) {
    $validation = [];
    $validation["method"] = (int)$method;
    $validation["timestamp"] = time();
    $validation["attestation"] = $attestation;

    return $validation;
  }

  public static function validateIncident($id, $method, $user, $userCheck = true, $stateCheck = true) {
    global $con;

    if ($user == "ME") $user = people::userData("id");
    if ($userCheck && !incidents::checkIncidentIsFromPerson($id, "ME", true)) return false;

    $incident = incidents::get($id, true);
    if ($incident === false || ($stateCheck && $incident["state"] !== incidents::STATE_REGISTERED)) return false;

    $attestation = self::createIncidentAttestation($incident, $method, $user);
    if ($attestation === false) return false;

    $validation = self::createValidation($method, $attestation);

    $svalidation = db::sanitize(json_encode($validation));
    $sid = (int)$incident["id"];

    return mysqli_query($con, "UPDATE incidents SET workervalidated = 1, workervalidation = '$svalidation' WHERE id = $sid LIMIT 1");
  }

  public static function validateRecord($id, $method, $user, $userCheck = true) {
    global $con;

    if ($user == "ME") $user = people::userData("id");
    if ($userCheck && !registry::checkRecordIsFromPerson($id, "ME", true)) return false;

    $record = registry::get($id, true);
    if ($record === false || $record["state"] !== registry::STATE_REGISTERED) return false;

    $attestation = self::createRecordAttestation($record, $method, $user);
    if ($attestation === false) return false;

    $validation = self::createValidation($method, $attestation);

    $svalidation = db::sanitize(json_encode($validation));
    $sid = (int)$record["id"];

    return mysqli_query($con, "UPDATE records SET workervalidated = 1, workervalidation = '$svalidation' WHERE id = $sid LIMIT 1");
  }

  public static function validate($method, $incidents, $records, $user = "ME") {
    if (!in_array($method, self::getAllowedMethods())) return -1;

    $incidents = self::string2array($incidents);
    $records = self::string2array($records);

    if ($user == "ME") $user = people::userData("id");

    $flag = false;
    foreach ($incidents as $incident) {
      if (!self::validateIncident($incident, $method, $user)) $flag = true;
    }
    foreach ($records as $record) {
      if (!self::validateRecord($record, $method, $user)) $flag = true;
    }

    return ($flag ? 1 : 0);
  }

  public static function getPeopleWithPendingValidations($gracePeriod = "DEFAULT") {
    if ($gracePeriod === "DEFAULT") $gracePeriod = self::reminderGracePeriod();

    $pendingPeople = [];

    $people = people::getAll();
    foreach ($people as $p) {
      $numPending = self::numPending((int)$p["id"], $gracePeriod);

      if ($numPending > 0) {
        $pending = [];
        $pending["person"] = $p;
        $pending["numPending"] = $numPending;

        $pendingPeople[] = $pending;
      }
    }

    return $pendingPeople;
  }

  public static function sendPendingValidationsReminder() {
    global $conf;

    if (!$conf["mail"]["enabled"]) {
      echo "[error] The mail functionality is not enabled in config.php.\n";
      return false;
    }

    if (!$conf["mail"]["capabilities"]["sendPendingValidationsReminder"]) {
      echo "[error] The pending validation reminders functionality is not inabled in config.php.\n";
      return false;
    }

    $pendingPeople = self::getPeopleWithPendingValidations();

    foreach ($pendingPeople as $p) {
      if (!isset($p["person"]["email"]) || empty($p["person"]["email"])) {
        echo "[info] ".$p["person"]["id"]." doesn't have an email address defined.\n";
        continue;
      }

      $to = [["email" => $p["person"]["email"]]];

      $subject = "[Recordatorio] Tienes validaciones pendientes en el aplicativo de control horario";
      $body = mail::bodyTemplate("<p>Hola ".security::htmlsafe(($p["person"]["name"]) ?? "").",</p>
      <p>Este es un mensaje automático para avisarte de que tienes ".(int)$p["numPending"]." incidencias y/o registros pendientes de validar en el aplicativo de control horario.</p>
      <p>Para validarlos, puedes acceder al aplicativo desde la siguiente dirección:</p>
      <ul>
        <li><a href='".security::htmlsafe($conf["fullPath"])."'>".security::htmlsafe($conf["fullPath"])."</a></li>
      </ul>");

      if (mail::send($to, [], $subject, $body)) {
        echo "[info] Mail sent to ".$p["person"]["id"]." successfuly.\n";
      } else {
        echo "[error] The email couldn't be sent to ".$p["person"]["id"]."\n";
      }
    }

    return true;
  }
}
