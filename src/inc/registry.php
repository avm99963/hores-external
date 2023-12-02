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

class registry {
  const LOGS_PAGINATION_LIMIT = 30;
  const REGISTRY_PAGINATION_LIMIT = 20;

  const STATE_REGISTERED = 0;
  const STATE_MANUALLY_INVALIDATED = 1;
  const STATE_VALIDATED_BY_WORKER = 2;

  public static $stateIcons = [
    0 => "check",
    1 => "delete_forever",
    2 => "verified_user"
  ];

  public static $stateIconColors = [
    0 => "mdl-color-text--green",
    1 => "mdl-color-text--red",
    2 => "mdl-color-text--green"
  ];

  public static $stateTooltips = [
    0 => "Registrado",
    1 => "Invalidado manualmente",
    2 => "Validado"
  ];

  public static $workerPendingWhere = "r.workervalidated = 0";
  public static $notInvalidatedWhere = "r.invalidated = 0";
  public static $logsWarnings = "LOCATE('[warning]', logdetails) as warningpos, LOCATE('[error]', logdetails) as errorpos, LOCATE('[fatalerror]', logdetails) as fatalerrorpos";

  private static function recordLog(&$log, $time, $executedby = -1, $quiet = false) {
    global $con;

    $slog = db::sanitize($log);
    $sday = (int)$time;
    $srealtime = (int)time();
    $sexecutedby = (int)$executedby;

    $status = mysqli_query($con, "INSERT INTO logs (realtime, day, executedby, logdetails) VALUES ($srealtime, $sday, $sexecutedby, '$slog')");

    if (!$status) {
      if (!$quiet) echo "[fatalerror] Couldn't record log into the database!\n";
      return false;
    } else {
      if (!$quiet) echo "[success] Log recorded into the database.\n";
      return mysqli_insert_id($con);
    }
  }

  public static function getLogs($start = 0, $limit = self::LOGS_PAGINATION_LIMIT) {
    global $con;

    $query = mysqli_query($con, "SELECT id, realtime, day, executedby, ".self::$logsWarnings." FROM logs ORDER BY id DESC".db::limitPagination($start, $limit));

    $return = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $return[] = $row;
    }

    return $return;
  }

  public static function getLog($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT logdetails, ".self::$logsWarnings." FROM logs WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return $row;
  }

  public static function beautifyLog($str) {
    $str = str_replace("[info]", "<span style='font-weight: bold;' class='mdl-color-text--blue'>[info]</span>", $str);
    $str = str_replace("[warning]", "<span style='font-weight: bold;' class='mdl-color-text--orange'>[warning]</span>", $str);
    $str = str_replace("[error]", "<span style='font-weight: bold;' class='mdl-color-text--red'>[error]</span>", $str);
    $str = str_replace("[fatalerror]", "<span style='font-weight: bold;' class='mdl-color-text--red-900'>[fatalerror]</span>", $str);
    return $str;
  }

  private static function addToLog(&$log, $quiet, $msg) {
    $log .= $msg;
    if (!$quiet) echo $msg;
  }

  private static function alreadyRegistered($time, $worker) {
    global $con;

    $stime = (int)$time;
    $sworker = (int)$worker;

    $query = mysqli_query($con, "SELECT id FROM records WHERE worker = $sworker AND day = $stime AND invalidated = 0 LIMIT 1");

    return (mysqli_num_rows($query) > 0);
  }

  private static function register($time, $worker, $schedule, $creator = -1) {
    global $con;

    $sworker = (int)$worker;
    $stime = (int)$time;
    $srealtime = (int)time();
    $screator = (int)$creator;
    $sbeginswork = (int)$schedule["beginswork"];
    $sendswork = (int)$schedule["endswork"];
    $sbeginsbreakfast = (int)$schedule["beginsbreakfast"];
    $sendsbreakfast = (int)$schedule["endsbreakfast"];
    $sbeginslunch = (int)$schedule["beginslunch"];
    $sendslunch = (int)$schedule["endslunch"];

    return mysqli_query($con, "INSERT INTO records (worker, day, created, creator, beginswork, endswork, beginsbreakfast, endsbreakfast, beginslunch, endslunch) VALUES ($sworker, $stime, $srealtime, $screator, $sbeginswork, $sendswork, $sbeginsbreakfast, $sendsbreakfast, $sbeginslunch, $sendslunch)");
  }

  public static function getWorkerCategory($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT p.category category FROM workers w INNER JOIN people p ON w.person = p.id WHERE w.id = $sid");

    if ($query === false || !mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    return $row["category"];
  }

  public static function getDayTypes($time) {
    global $con;

    $stime = (int)$time;

    $query = mysqli_query($con, "SELECT id, category, details FROM calendars WHERE begins <= $stime AND ends >= $stime");
    if ($query === false) return false;

    $calendars = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $calendar = json_decode($row["details"], true);
      if (json_last_error() !== JSON_ERROR_NONE) return false;
      if (!isset($calendar[$time])) return false;

      $calendars[$row["category"]] = $calendar[$time];
    }

    return $calendars;
  }

  private static function getApplicableSchedules($time) {
    global $con;

    $stime = (int)$time;

    $query = mysqli_query($con, "SELECT id FROM schedules WHERE begins <= $stime AND ends >= $stime AND active = 1");
    if ($query === false) return false;

    $schedules = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $schedules[] = $row["id"];
    }

    return $schedules;
  }

  public static function generateNow($originaltime, &$logId, $quiet = true, $executedby = -1, $workersWhitelist = false) {
    global $con;

    $log = "";

    if ($workersWhitelist !== false) self::addToLog($log, $quiet, "[info] This is a partial registry generation, because a whitelist of workers was passed: [".implode(", ", $workersWhitelist)."]\n");

    $datetime = new DateTime();
    $datetime->setTimestamp($originaltime);
    self::addToLog($log, $quiet, "[info] Time passed: ".$datetime->format("Y-m-d H:i:s")."\n");

    $rawdate = $datetime->format("Y-m-d")."T00:00:00";
    self::addToLog($log, $quiet, "[info] Working with this date: $rawdate\n");
    $date = new DateTime($rawdate);

    $time = $date->getTimestamp();
    $dow = (int)$date->format("N") - 1;
    self::addToLog($log, $quiet, "[info] Final date timestamp: $time, Dow: $dow\n");

    $days = self::getDayTypes($time);
    if ($days === false) {
      self::addToLog($log, $quiet, "[fatalerror] An error occurred while loading the calendars.\n");
      $logId = self::recordLog($log, $time, $executedby, $quiet);
      return 1;
    }

    $schedules = self::getApplicableSchedules($time);
    if ($schedules === false) {
      self::addToLog($log, $quiet, "[fatalerror] An error occurred while loading the active schedules.\n");
      $logId = self::recordLog($log, $time, $executedby, $quiet);
      return 2;
    }

    self::addToLog($log, $quiet, "[info] Found ".count($schedules)." active schedule(s)\n");

    foreach ($schedules as $scheduleid) {
      self::addToLog($log, $quiet, "\n[info] Processing schedule $scheduleid\n");

      $s = schedules::get($scheduleid);
      if ($s === false) {
        self::addToLog($log, $quiet, "[fatalerror] An error ocurred while loading schedule with id $scheduleid (it doesn't exist or there was an error with the SQL query)\n");
        $logId = self::recordLog($log, $time, $executedby, $quiet);
        return 3;
      }

      if ($workersWhitelist !== false && !in_array($s["worker"], $workersWhitelist)) {
        self::addToLog($log, $quiet, "[info] This schedule's worker (".$s["worker"].") is not in the whitelist, so skipping\n");
        continue;
      }

      $category = self::getWorkerCategory($s["worker"]);

      if (isset($days[$category])) {
        self::addToLog($log, $quiet, "[info] Using worker's (".$s["worker"].") category ($category) calendar\n");
        $typeday = $days[$category];
      } else if (isset($days[-1])) {
        self::addToLog($log, $quiet, "[info] Using default calendar\n");
        $typeday = $days[-1];
      } else {
        self::addToLog($log, $quiet, "[warning] No calendar applies, so skipping this schedule\n");
        continue;
      }

      if (!isset($s["days"][$typeday])) {
        self::addToLog($log, $quiet, "[info] This schedule doesn't have this type of day ($typeday) set up, so skipping\n");
        continue;
      }

      if (!isset($s["days"][$typeday][$dow])) {
        self::addToLog($log, $quiet, "[info] This schedule doesn't have a daily schedule for this day of the week ($dow) and type of day ($typeday), so skipping.\n");
        continue;
      }

      self::addToLog($log, $quiet, "[info] Found matching daily schedule. We'll proceed to register it\n");

      if (self::alreadyRegistered($time, $s["worker"])) {
        self::addToLog($log, $quiet, "[warning] We're actually NOT going to register it because another registry already exists for this worker at the same day.\n");
      } else {
        if (self::register($time, $s["worker"], $s["days"][$typeday][$dow], $executedby)) {
          self::addToLog($log, $quiet, "[info] Registered with id ".mysqli_insert_id($con)."\n");
        } else {
          self::addToLog($log, $quiet, "[error] Couldn't register this schedule because of an unknown error!\n");
        }
      }
    }

    $logId = self::recordLog($log, $time, $executedby, $quiet);

    return 0;
  }

  public static function getStatus($row) {
    if ($row["invalidated"] == 1) return self::STATE_MANUALLY_INVALIDATED;
    elseif ($row["workervalidated"] == 1) return self::STATE_VALIDATED_BY_WORKER;
    else return self::STATE_REGISTERED;
  }

  private static function magicRecord(&$row) {
    $row["state"] = self::getStatus($row);
  }

  public static function get($id, $magic = false) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM records WHERE id = $sid LIMIT 1");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);
    if ($magic) self::magicRecord($row);

    return $row;
  }

  public static function getRecords($worker = false, $begins = false, $ends = false, $returnInvalid = false, $includeWorkerInfo = false, $sortByDateDesc = false, $start = 0, $limit = self::REGISTRY_PAGINATION_LIMIT, $treatWorkerAttributeAsUser = false, $onlyWorkerPending = false, $magic = true) {
    global $con;

    if ($treatWorkerAttributeAsUser && !$includeWorkerInfo) return false;

    $where = [];

    if ($worker !== false) $where[] = ($treatWorkerAttributeAsUser ? "w.person" : "r.worker")." = ".(int)$worker;

    $dateLimit = ($begins !== false && $ends !== false);
    if ($dateLimit) {
      $where[] = "r.day >= ".(int)$begins;
      $where[] = "r.day <= ".(int)$ends;
    }

    if (!$returnInvalid || $onlyWorkerPending) $where[] = self::$notInvalidatedWhere;

    if ($onlyWorkerPending) $where[] = self::$workerPendingWhere;

    $query = mysqli_query($con, "SELECT r.*".($includeWorkerInfo ? ", p.id personid, p.name workername, w.company companyid" : "")." FROM records r LEFT JOIN workers w ON r.worker = w.id".($includeWorkerInfo ? " LEFT JOIN people p ON w.person = p.id" : "").(count($where) ? " WHERE ".implode(" AND ", $where) : "")." ORDER BY".($sortByDateDesc ? " r.day DESC, w.company DESC," : "")." id DESC".db::limitPagination($start, $limit));

    $return = [];
    while ($row = mysqli_fetch_assoc($query)) {
      if ($magic) self::magicRecord($row);
      $return[] = $row;
    }

    return $return;
  }

  public static function getWorkerRecords($worker, $begins = false, $ends = false, $returnInvalid = false, $onlyWorkerPending = false) {
    return self::getRecords($worker, $begins, $ends, $returnInvalid, false, true, 0, 0, false, $onlyWorkerPending);
  }

  public static function numRows($includingInvalidated = false) {
    global $con;

    $query = mysqli_query($con, "SELECT COUNT(*) count FROM records".($includingInvalidated ? "" : " WHERE invalidated = 0"));

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return (isset($row["count"]) ? (int)$row["count"] : false);
  }

  public static function numRowsUser($user, $includingInvalidated = true) {
    global $con;

    $where = [];
    if (!$includingInvalidated) $where[] = "r.invlidated = 0";
    $where[] = "w.person = ".(int)$user;

    $query = mysqli_query($con, "SELECT COUNT(*) count FROM records r LEFT JOIN workers w ON r.worker = w.id".(count($where) ? " WHERE ".implode(" AND ", $where) : ""));

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return (isset($row["count"]) ? (int)$row["count"] : false);
  }

  public static function checkRecordIsFromPerson($record, $person = "ME", $boolean = false) {
    global $con;

    if ($person == "ME") $person = people::userData("id");

    $srecord = (int)$record;
    $sperson = (int)$person;

    $query = mysqli_query($con, "SELECT r.id FROM records r INNER JOIN workers w ON r.worker = w.id INNER JOIN people p ON w.person = p.id WHERE p.id = $sperson AND r.id = $srecord LIMIT 1");

    if (!mysqli_num_rows($query)) {
      if ($boolean) return false;
      security::denyUseMethod(security::METHOD_NOTFOUND);
    }

    return true;
  }

  public static function invalidate($id, $invalidatedby = "ME") {
    global $con;

    $sid = (int)$id;

    if ($invalidatedby === "ME") $invalidatedby = people::userData("id");
    $sinvalidatedby = (int)$invalidatedby;

    return mysqli_query($con, "UPDATE records SET invalidated = 1, invalidatedby = $sinvalidatedby WHERE id = $sid LIMIT 1");
  }

  public static function invalidateAll($id, $beginsRawDate, $endsRawDate, $invalidatedby = "ME") {
    global $con;

    $beginsDate = new DateTime($beginsRawDate);
    $sbegins = (int)$beginsDate->getTimestamp();
    $endsDate = new DateTime($endsRawDate);
    $sends = (int)$endsDate->getTimestamp();

    $sid = (int)$id;

    if ($invalidatedby === "ME") $invalidatedby = people::userData("id");
    $sinvalidatedby = (int)$invalidatedby;

    return mysqli_query($con, "UPDATE records SET invalidated = 1, invalidatedby = $sinvalidatedby WHERE worker = $sid AND invalidated = 0 AND day <= $sends AND day >= $sbegins");
  }
}
