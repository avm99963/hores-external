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

class incidents {
  const STARTOFDAY = 0;
  const ENDOFDAY = 60*60*24;

  const PAGINATION_LIMIT = 20;

  const UPDATE_STATE_NOT_UPDATED = 0;
  const UPDATE_STATE_UPDATED = 1;

  const STATE_UNVERIFIED = 0;
  const STATE_REJECTED = 1;
  const STATE_SCHEDULED = 2;
  const STATE_REGISTERED = 3;
  const STATE_MANUALLY_INVALIDATED = 4;
  const STATE_VALIDATED_BY_WORKER = 5;

  public static $stateIcons = array(
    0 => "new_releases",
    1 => "block",
    2 => "schedule",
    3 => "check",
    4 => "delete_forever",
    5 => "verified_user"
  );

  public static $stateIconColors = array(
    0 => "mdl-color-text--orange",
    1 => "mdl-color-text--red",
    2 => "mdl-color-text--orange",
    3 => "mdl-color-text--green",
    4 => "mdl-color-text--red",
    5 => "mdl-color-text--green"
  );

  public static $stateTooltips = array(
    0 => "Pendiente de revisión",
    1 => "Rechazada al revisar",
    2 => "Programada",
    3 => "Registrada",
    4 => "Invalidada manualmente",
    5 => "Validada"
  );

  public static $statesOrderForFilters = [0, 1, 4, 2, 3, 5];

  public static $invalidStates = [incidents::STATE_REJECTED, incidents::STATE_MANUALLY_INVALIDATED];

  public static $cannotEditCommentsStates = [];
  public static $cannotEditStates = [self::STATE_VALIDATED_BY_WORKER, self::STATE_REGISTERED, self::STATE_MANUALLY_INVALIDATED, self::STATE_REJECTED];
  public static $canRemoveStates = [self::STATE_SCHEDULED];
  public static $canInvalidateStates = [self::STATE_VALIDATED_BY_WORKER, self::STATE_REGISTERED];
  public static $workerCanEditStates = [self::STATE_UNVERIFIED];
  public static $workerCanRemoveStates = [self::STATE_UNVERIFIED];

  public static $adminPendingWhere = "i.verified = 0 AND i.confirmedby = -1";
  public static $workerPendingWhere = "i.workervalidated = 0";
  public static $activeWhere = "i.verified = 1 AND i.invalidated = 0";
  public static $notInvalidatedOrRejectedWhere = "i.invalidated = 0 AND (i.verified = 1 OR i.confirmedby = -1)";

  const FILTER_TYPE_ARRAY = 0;
  const FILTER_TYPE_INT = 1;
  const FILTER_TYPE_STRING = 2;

  public static $filters = ["begins", "ends", "types", "states", "attachments", "details", "workerdetails"];
  public static $filtersType = [
    "begins" => self::FILTER_TYPE_STRING,
    "ends" => self::FILTER_TYPE_STRING,
    "types" => self::FILTER_TYPE_ARRAY,
    "states" => self::FILTER_TYPE_ARRAY,
    "attachments" => self::FILTER_TYPE_ARRAY,
    "details" => self::FILTER_TYPE_ARRAY,
    "workerdetails" => self::FILTER_TYPE_ARRAY
  ];

  public static $filtersSwitch = ["attachments", "details", "workerdetails"];
  public static $filtersSwitchOptions = [
    0 => "Sin",
    1 => "Con"
  ];
  public static $filtersSwitchHelper = [
    "attachments" => "archivo adjunto",
    "details" => "observaciones de un administrador",
    "workerdetails" => "observaciones del trabajador"
  ];
  public static $filtersSwitchMysqlField = [
    "attachments" => "attachments",
    "details" => "details",
    "workerdetails" => "workerdetails"
  ];

  public static function getTypes($showHidden = true, $isForWorker = false) {
    global $con, $conf;

    $whereConditions = [];
    if (!$showHidden) $whereConditions[] = "hidden = 0";
    if ($isForWorker) $whereConditions[] = "workerfill = 1";

    $where = (count($whereConditions) ? " WHERE ".implode(" AND ",$whereConditions) : "");

    $query = mysqli_query($con, "SELECT * FROM typesincidents".$where." ORDER BY ".($conf["debug"] ? "id ASC" : "hidden ASC, name ASC"));

    $incidents = [];

    while ($row = mysqli_fetch_assoc($query)) {
      $incidents[] = $row;
    }

    return $incidents;
  }

  public static function getTypesForm($isForWorker = false) {
    return self::getTypes(false, $isForWorker);
  }

  public static function getType($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM typesincidents WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function addType($name, $present, $paid, $workerfill, $notifies, $autovalidates, $hidden) {
    global $con;

    $sname = db::sanitize($name);
    $spresent = (int)$present;
    $spaid = (int)$paid;
    $sworkerfill = (int)$workerfill;
    $snotifies = (int)$notifies;
    $sautovalidates = (int)$autovalidates;
    $shidden = (int)$hidden;

    return mysqli_query($con, "INSERT INTO typesincidents (name, present, paid, workerfill, notifies, autovalidates, hidden) VALUES ('$name', $spresent, $spaid, $sworkerfill, $snotifies, $sautovalidates, $shidden)");
  }

  public static function editType($id, $name, $present, $paid, $workerfill, $notifies, $autovalidates, $hidden) {
    global $con;

    $sid = (int)$id;
    $sname = db::sanitize($name);
    $spresent = (int)$present;
    $spaid = (int)$paid;
    $sworkerfill = (int)$workerfill;
    $snotifies = (int)$notifies;
    $sautovalidates = (int)$autovalidates;
    $shidden = (int)$hidden;

    return mysqli_query($con, "UPDATE typesincidents SET name = '$name', present = $spresent, paid = $spaid, workerfill = $sworkerfill, notifies = $snotifies, autovalidates = $sautovalidates, hidden = $shidden WHERE id = $sid LIMIT 1");
  }

  public static function addWhereConditionsHandledBySelect($select, &$whereConditions, $alreadyFilteredDates = false) {
    if (!$alreadyFilteredDates) {
      if ($select["enabled"]["begins"]) $whereConditions[] = "i.day >= ".(int)common::getTimestampFromRFC3339($select["selected"]["begins"]);
      if ($select["enabled"]["ends"]) $whereConditions[] = "i.day <= ".(int)common::getTimestampFromRFC3339($select["selected"]["ends"]);
    }

    if ($select["enabled"]["types"]) {
      $insideconditions = [];
      foreach ($select["selected"]["types"] as $type) {
        $insideconditions[] = "i.type = ".(int)$type;
      }
      $whereConditions[] = "(".implode(" OR ", $insideconditions).")";
    }

    foreach (self::$filtersSwitch as $f) {
      if ($select["enabled"][$f]) {
        foreach ($select["selected"][$f] as $onoff) {
          $mysqlField = self::$filtersSwitchMysqlField[$f];

          if ($onoff == "0") $insideconditions[] = "($mysqlField IS NULL OR $mysqlField = ''".($f == "attachments" ? " OR $mysqlField = '[]'" : "").")";
          else if ($onoff == "1") $insideconditions[] = "($mysqlField IS NOT NULL AND $mysqlField <> ''".($f == "attachments" ? " AND $mysqlField <> '[]'" : "").")";
        }
        if (count($insideconditions) == 1) $whereConditions[] = $insideconditions[0];
      }
    }
  }

  public static function incidentIsWantedAccordingToSelect($select, $incident) {
    if ($select["enabled"]["states"]) {
      return in_array($incident["state"], $select["selected"]["states"]);
    }

    return true;
  }

  public static function todayPage($limit = self::PAGINATION_LIMIT) {
    global $con;

    $today = (int)common::getDayTimestamp(time());

    $query = mysqli_query($con, "SELECT COUNT(*) count FROM incidents i WHERE i.day > $today");
    if ($query === false) return 0;

    $row = mysqli_fetch_assoc($query);
    $first = $row["count"];

    return floor($first/(int)$limit) + 1;
  }

  public static function numRows($select = null, $onlyAdminPending = false) {
    global $con;

    $whereConditions = [];

    if ($select !== null) {
      if (!$select["showResultsPaginated"]) return 0;

      self::addWhereConditionsHandledBySelect($select, $whereConditions);
    }

    if ($onlyAdminPending) $whereConditions[] = self::$adminPendingWhere;

    $where = (count($whereConditions) ? " WHERE ".implode(" AND ", $whereConditions) : "");
    $query = mysqli_query($con, "SELECT COUNT(*) count FROM incidents i".$where);

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return (isset($row["count"]) ? (int)$row["count"] : false);
  }

  public static function numPending() {
    return self::numRows(null, true);
  }

  public static function getStatus(&$incident, $beginsdatetime = -1) {
    if ($beginsdatetime == -1) {
      $date = new DateTime();
      $date->setTimestamp($incident["day"]);
      $dateFormat = $date->format("Y-m-d");

      $begins = new DateTime($dateFormat."T".schedules::sec2time((int)$incident["begins"], false).":00");
      $beginsdatetime = $begins->getTimestamp();
    }

    $time = time();

    if ($incident["verified"] == 0 && $incident["confirmedby"] == -1) return self::STATE_UNVERIFIED;
    elseif ($incident["verified"] == 0) return self::STATE_REJECTED;
    elseif ($incident["invalidated"] == 1) return self::STATE_MANUALLY_INVALIDATED;
    elseif ($beginsdatetime >= $time) return self::STATE_SCHEDULED;
    elseif ($incident["workervalidated"] == 1) return self::STATE_VALIDATED_BY_WORKER;
    else return self::STATE_REGISTERED;
  }

  public static function getAttachmentsFromIncident(&$incident) {
    if (empty($incident["attachments"]) || $incident["attachments"] === null) return [];

    $json = json_decode($incident["attachments"], true);
    if (json_last_error() !== JSON_ERROR_NONE) return false;

    return $json;
  }

  public static function getAttachments($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT attachments FROM incidents WHERE id = ".$sid);
    if (!mysqli_num_rows($query)) return false;

    $incident = mysqli_fetch_assoc($query);

    return self::getAttachmentsFromIncident($incident);
  }

  public static function addAttachment($id, &$file) {
    global $con;

    $name = "";
    $status = files::uploadFile($file, $name);
    if ($status !== 0) return $status;

    $attachments = self::getAttachments($id);
    $attachments[] = $name;

    $sid = (int)$id;
    $srawAttachments = db::sanitize(json_encode($attachments));

    return (mysqli_query($con, "UPDATE incidents SET attachments = '".$srawAttachments."' WHERE id = $sid LIMIT 1") ? 0 : 1);
  }

  public static function deleteAttachment($id, $name = "ALL") {
    global $con;

    $incident = incidents::get($id, true);
    if ($incident === false) return false;

    $attachments = incidents::getAttachmentsFromIncident($incident);

    if ($attachments === false) return false;
    if (!count($attachments)) return ($name == "ALL");

    $flag = false;

    foreach ($attachments as $i => $attachment) {
      if ($attachment == $name || $name === "ALL") {
        $flag = true;

        if (!files::removeFile($attachment)) return false;

        unset($attachments[$i]);
        $attachments = array_values($attachments);

        $sid = (int)$id;
        $srawAttachments = db::sanitize(json_encode($attachments));
      }
    }

    return ($flag ? mysqli_query($con, "UPDATE incidents SET attachments = '".$srawAttachments."' WHERE id = $sid LIMIT 1") : false);
  }

  private static function magicIncident(&$row) {
    $row["allday"] = ($row["begins"] == self::STARTOFDAY && $row["ends"] == self::ENDOFDAY);
    $row["updatestate"] = ($row["updatedby"] == -1 ? self::UPDATE_STATE_NOT_UPDATED : self::UPDATE_STATE_UPDATED);

    $date = new DateTime();
    $date->setTimestamp($row["day"]);
    $dateFormat = $date->format("Y-m-d");

    $begins = new DateTime($dateFormat."T".schedules::sec2time((int)$row["begins"], false).":00");
    $row["beginsdatetime"] = $begins->getTimestamp();
    $ends = new DateTime($dateFormat."T".schedules::sec2time((int)$row["ends"], false).":00");
    $row["endsdatetime"] = $ends->getTimestamp();

    $row["state"] = self::getStatus($row, $row["beginsdatetime"]);
  }

  public static function get($id, $magic = false) {
    global $con, $conf;

    $sid = $id;

    $query = mysqli_query($con, "SELECT * FROM incidents WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);
    if ($magic) self::magicIncident($row);

    return $row;
  }

  public static function getAll($onlyAdminPending = false, $start = 0, $limit = self::PAGINATION_LIMIT, $worker = "ALL", $begins = null, $ends = null, $onlyWorkerPending = false, $select = false) {
    global $con, $conf;

    $whereConditions = [];
    if ($onlyAdminPending) $whereConditions[] = self::$adminPendingWhere;
    if ($onlyWorkerPending) {
      $whereConditions[] = self::$workerPendingWhere;
      $whereConditions[] = self::$activeWhere;
    }
    if ($worker !== "ALL") $whereConditions[] = "i.worker = ".(int)$worker;
    if ($begins !== null && $ends !== null) {
      $whereConditions[] = "i.day <= ".(int)$ends." AND i.day >= ".(int)$begins;
      $filteredDates = true;
    } else $filteredDates = false;

    if ($select !== false) self::addWhereConditionsHandledBySelect($select, $whereConditions, $filteredDates);

    $where = (count($whereConditions) ? " WHERE ".implode(" AND ", $whereConditions) : "");

    $query = mysqli_query($con, "SELECT i.*, t.id typeid, t.name typename, t.present typepresent, t.paid typepaid, t.workerfill typeworkerfill, t.notifies typenotifies, t.hidden typehidden, p.id personid, p.name workername, w.company companyid FROM incidents i LEFT JOIN typesincidents t ON i.type = t.id LEFT JOIN workers w ON i.worker = w.id LEFT JOIN people p ON w.person = p.id".$where." ORDER BY i.day DESC".db::limitPagination($start, $limit));

    $return = [];
    while ($row = mysqli_fetch_assoc($query)) {
      self::magicIncident($row);
      if ($onlyWorkerPending && $row["state"] !== self::STATE_REGISTERED) continue;
      if ($select !== false && !self::incidentIsWantedAccordingToSelect($select, $row)) continue;
      $return[] = $row;
    }

    return $return;
  }

  public static function checkOverlap($worker, $day, $begins, $ends, $sans = 0) {
    global $con;

    $sworker = (int)$worker;
    $sday = (int)$day;
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    $ssans = (int)$sans;

    $query = mysqli_query($con, "SELECT * FROM incidents i WHERE begins <= $sends AND ends >= $sbegins AND day = $sday AND worker = $sworker".($sans == 0 ? "" : " AND id <> $ssans")." AND ".self::$notInvalidatedOrRejectedWhere." LIMIT 1");

    return (mysqli_num_rows($query) > 0);
  }

  public static function add($worker, $type, $details, $iday, $begins, $ends, $creator = "ME", $verified = 1, $alreadyTimestamp = false, $isWorkerView = false, $sendEmail = true, $forceAutoValidate = false) {
    global $con, $conf;

    // Gets information about the worker
    $sworker = (int)$worker;
    $workerInfo = workers::get($sworker);
    if ($workerInfo === false) return 1;

    // Sets who is the incident creator
    if ($creator === "ME") $creator = people::userData("id");
    $screator = (int)$creator;

    // If the user is not an admin and the person to who we are going to add the incident is not the creator of the incident, do not continue
    if (!security::isAllowed(security::ADMIN) && $workerInfo["person"] != $creator) return 5;

    // Sanitizes other incident fields
    $stype = (int)$type;
    $sverified = (int)$verified;
    $sdetails = db::sanitize($details);

    // Gets information about the incident type
    $incidenttype = self::getType($stype);
    if ($incidenttype === false) return -1;

    // Gets the timestamp of the incident day
    if ($alreadyTimestamp) {
      $sday = (int)$iday;
    } else {
      $day = new DateTime($iday);
      $sday = (int)$day->getTimestamp();
    }

    // Gets the start and end times, and checks whether they are well-formed
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    if ($sbegins >= $sends) return 3;

    // Checks whether the incident overlaps another incident
    if (self::checkOverlap($worker, $sday, $begins, $ends)) return 2;

    // Adds the incident
    if (!mysqli_query($con, "INSERT INTO incidents (worker, creator, type, day, begins, ends, ".($isWorkerView ? "workerdetails" : "details").", verified) VALUES ($sworker, $screator, $stype, $sday, $sbegins, $sends, '$sdetails', $sverified)")) return -1;

    // If the incident type is set to autovalidate or we pass the parameter to force the autovalidation, autovalidate it
    if (($incidenttype["autovalidates"] == 1 || $forceAutoValidate) && !validations::validateIncident(mysqli_insert_id($con), validations::METHOD_AUTOVALIDATION, "ME", false, false)) {
      return 5;
    }

    // Bonus: check whether we should send email notifications, and if applicable send them
    if ($sendEmail && $conf["mail"]["enabled"] && ($conf["mail"]["capabilities"]["notifyOnWorkerIncidentCreation"] || $conf["mail"]["capabilities"]["notifyOnAdminIncidentCreation"] || $conf["mail"]["capabilities"]["notifyCategoryResponsiblesOnIncidentCreation"])) {
      $workerName = people::workerData("name", $sworker);

      $to = [];
      if (($conf["mail"]["capabilities"]["notifyOnWorkerIncidentCreation"] && $isWorkerView) || ($conf["mail"]["capabilities"]["notifyOnAdminIncidentCreation"] && !$isWorkerView)) {
        $to[] = array("email" => $conf["mail"]["adminEmail"]);
      }

      if ($conf["mail"]["capabilities"]["notifyCategoryResponsiblesOnIncidentCreation"] && $incidenttype["notifies"] == 1) {
        $categoryid = people::workerData("category", $sworker);
        $category = categories::get($categoryid);
        if ($category === false) return 0;

        $emails = json_decode($category["emails"], true);
        if (json_last_error() === JSON_ERROR_NONE) {
          foreach ($emails as $email) {
            $to[] = array("email" => $email);
          }
        }
      }

      if (!count($to)) return 0;

      $subject = "Incidencia del tipo \"".security::htmlsafe($incidenttype["name"])."\" creada para ".security::htmlsafe($workerName)." el ".date::getShortDate($sday);
      $body = mail::bodyTemplate("<p>Hola,</p>
      <p>Este es un mensaje automático para avisarte de que ".security::htmlsafe(people::userData("name"))." ha introducido la siguiente incidencia en el sistema de registro horario:</p>
      <ul>
        <li><b>Trabajador:</b> ".security::htmlsafe($workerName)."</li>
        <li><b>Motivo:</b> ".security::htmlsafe($incidenttype["name"])."</li>
        <li><b>Fecha:</b> ".date::getShortDate($sday)." ".(($sbegins == 0 && $sends == self::ENDOFDAY) ? "(todo el día)": schedules::sec2time($sbegins)."-".schedules::sec2time($sends))."</li>".
        (!empty($details) ? "<li><b>Observaciones:</b> <span style='white-space: pre-wrap;'>".security::htmlsafe($details)."</span></li>" : "").
      "</ul>
      <p style='font-size: 11px;'>Has recibido este mensaje porque estás configurado como persona responsable de la categoría a la que pertenece este trabajador o eres el administrador del sistema.</p>");

      return (mail::send($to, [], $subject, $body) ? 0 : 4);
    }

    return 0;
  }

  public static function edit($id, $type, $iday, $begins, $ends, $updatedby = "ME") {
    global $con;

    $sid = (int)$id;

    $incident = incidents::get($id);
    if ($incident === false) return 1;

    $stype = (int)$type;
    if ($updatedby === "ME") $updatedby = people::userData("id");
    $supdatedby = (int)$updatedby;

    $day = new DateTime($iday);
    $sday = (int)$day->getTimestamp();

    $sbegins = (int)$begins;
    $sends = (int)$ends;
    if ($sbegins >= $sends) return 3;
    if (self::checkOverlap($incident["worker"], $sday, $begins, $ends, $id)) return 2;

    return (mysqli_query($con, "UPDATE incidents SET type = $stype, day = $sday, begins = $sbegins, ends = $sends, updatedby = $supdatedby, workervalidated = 0, workervalidation = '' WHERE id = $sid LIMIT 1") ? 0 : -1);
  }

  public static function editDetails($id, $details, $updatedby = "ME") {
    global $con;

    $sid = (int)$id;
    $sdetails = db::sanitize($details);
    if ($updatedby === "ME") $updatedby = people::userData("id");
    $supdatedby = (int)$updatedby;

    $incident = self::get($sid);
    if ($incident === false) return -1;

    $status = self::getStatus($incident);
    if (in_array($status, self::$cannotEditCommentsStates)) return 1;

    return (mysqli_query($con, "UPDATE incidents SET details = '$sdetails', updatedby = '$supdatedby' WHERE id = $sid LIMIT 1") ? 0 : -1);
  }

  public static function verify($id, $value, $confirmedby = "ME") {
    global $con, $conf;

    $sid = (int)$id;
    $svalue = ($value == 1 ? 1 : 0);
    if ($confirmedby === "ME") $confirmedby = people::userData("id");
    $sconfirmedby = (int)$confirmedby;

    $incident = incidents::get($id);
    if ($incident === false) return false;

    $state = incidents::getStatus($incident);
    if ($state != incidents::STATE_UNVERIFIED) return false;

    if (!mysqli_query($con, "UPDATE incidents SET verified = $svalue, confirmedby = $sconfirmedby WHERE id = $sid LIMIT 1")) return false;

    if ($conf["mail"]["enabled"] && $conf["mail"]["capabilities"]["notifyWorkerOnIncidentDecision"]) {
      $workerEmail = people::workerData("email", $incident["worker"]);
      if ($workerEmail !== false && !empty($workerEmail)) {
        $workerName = people::workerData("name", $incident["worker"]);

        $to = [array(
          "email" => $workerEmail,
          "name" => $workerName
        )];
        $subject = "Incidencia del ".date::getShortDate($incident["day"])." ".($value == 1 ? "verificada" : "rechazada");
        $body = mail::bodyTemplate("<p>Bienvenido ".security::htmlsafe($workerName).",</p>
        <p>Este es un mensaje automático para avisarte de que la incidencia que introduciste para el día ".date::getLongDate($incident["day"])." ha sido ".($value == 1 ? "aceptada" : "rechazada").".</p><p>Puedes ver el estado de todas tus incidencias en el <a href='".security::htmlsafe($conf["fullPath"])."'>aplicativo web</a>.</p>");

        mail::send($to, [], $subject, $body);
      }
    }

    return true;
  }

  public static function remove($id) {
    global $con;

    $sid = (int)$id;

    $incident = incidents::get($id);
    if ($incident === false) return false;

    if (!self::deleteAttachment($id, "ALL")) return false;

    return mysqli_query($con, "DELETE FROM incidents WHERE id = $sid LIMIT 1");
  }

  public static function invalidate($id, $updatedby = "ME") {
    global $con;

    $sid = (int)$id;
    if ($updatedby === "ME") $updatedby = people::userData("id");
    $supdatedby = (int)$updatedby;

    $incident = incidents::get($id);
    if ($incident === false) return false;

    $state = incidents::getStatus($incident);
    if (!in_array($state, self::$canInvalidateStates)) return false;

    return mysqli_query($con, "UPDATE incidents SET invalidated = 1, updatedby = $supdatedby WHERE id = $sid LIMIT 1");
  }

  public static function checkIncidentIsFromPerson($incident, $person = "ME", $boolean = false) {
    global $con;

    if ($person == "ME") $person = people::userData("id");

    $sincident = (int)$incident;
    $sperson = (int)$person;

    $query = mysqli_query($con, "SELECT i.id FROM incidents i INNER JOIN workers w ON i.worker = w.id INNER JOIN people p ON w.person = p.id WHERE p.id = $sperson AND i.id = $sincident LIMIT 1");

    if (!mysqli_num_rows($query)) {
      if ($boolean) return false;
      security::denyUseMethod(security::METHOD_NOTFOUND);
    }

    return true;
  }
}
