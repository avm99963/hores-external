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

class schedules {
  const STATUS_NO_ACTIVE_SCHEDULE = 0;
  const STATUS_HALFWAY_CONFIGURED_SCHEDULE = 1;
  const STATUS_ACTIVE_SCHEDULE = 2;

  public static $allEvents = ["work", "breakfast", "lunch"];
  public static $otherEvents = ["breakfast", "lunch"];
  public static $otherEventsDescription = [
    "breakfast" => "Desayuno",
    "lunch" => "Comida"
  ];
  public static $workerScheduleStatus = [
    0 => "No hay ningún calendario activo",
    1 => "Hay un calendario activo pero no está completamente configurado",
    2 => "Hay un calendario activo completamente configurado"
  ];
  public static $workerScheduleStatusShort = [
    0 => "No hay un horario activo",
    1 => "No está configurado del todo",
    2 => "Existe uno configurado"
  ];
  public static $workerScheduleStatusColors = [
    0 => "red",
    1 => "orange",
    2 => "green"
  ];

  public static function time2sec($time) {
    $e = explode(":", $time);
    return ((int)$e[0]*60 + (int)$e[1])*60;
  }

  public static function sec2time($sec, $autoShowSeconds = true) {
    $min = floor($sec/60);
    $secr = $sec % 60;
    return visual::padNum(floor($min/60), 2).":".visual::padNum(($min % 60), 2).($autoShowSeconds && $secr != 0 ? ":".visual::padNum($secr, 2) : "");
  }

  // TEMPLATES:

  public static function addTemplate($name, $ibegins, $iends) {
    global $con;

    $sname = db::sanitize($name);
    $begins = new DateTime($ibegins);
    $sbegins = (int)$begins->getTimestamp();
    $ends = new DateTime($iends);
    $sends = (int)$ends->getTimestamp();

    if (!intervals::wellFormed([$sbegins, $sends])) return 2;

    return (mysqli_query($con, "INSERT INTO scheduletemplates (name, begins, ends) VALUES ('$sname', $sbegins, $sends)") ? 0 : 1);
  }

  private static function _addDaysToReturn(&$return, $stableDays, $sfieldSchedule, $sid) {
    global $con;

    $return["days"] = [];

    $query2 = mysqli_query($con, "SELECT * FROM $stableDays WHERE $sfieldSchedule = $sid ORDER BY typeday ASC, day ASC");

    while ($row = mysqli_fetch_assoc($query2)) {
      if (!isset($return["days"][$row["typeday"]]))
        $return["days"][$row["typeday"]] = [];

      $return["days"][$row["typeday"]][$row["day"]] = $row;
    }
  }

  private static function _get($id, $table, $tableDays, $fieldSchedule) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);
    $stableDays = preg_replace("/[^A-Za-z0-9 ]/", '', $tableDays);
    $sfieldSchedule = preg_replace("/[^A-Za-z0-9 ]/", '', $fieldSchedule);

    $query = mysqli_query($con, "SELECT * FROM $stable WHERE id = $sid");

    if ($query === false || !mysqli_num_rows($query)) return false;

    $return = mysqli_fetch_assoc($query);

    self::_addDaysToReturn($return, $stableDays, $sfieldSchedule, $sid);

    return $return;
  }

  public static function getTemplate($id) {
    return self::_get($id, "scheduletemplates", "scheduletemplatesdays", "template");
  }

  public static function getTemplates() {
    global $con, $conf;

    $query = mysqli_query($con, "SELECT * FROM scheduletemplates ORDER BY ".($conf["debug"] ? "id" : "name")." ASC");

    $templates = [];

    while ($row = mysqli_fetch_assoc($query)) {
      $templates[] = $row;
    }

    return $templates;
  }

  public static function editTemplate($id, $name, $ibegins, $iends) {
    global $con;

    $sid = (int)$id;
    $sname = db::sanitize($name);
    $begins = new DateTime($ibegins);
    $sbegins = (int)$begins->getTimestamp();
    $ends = new DateTime($iends);
    $sends = (int)$ends->getTimestamp();

    if (!intervals::wellFormed([$sbegins, $sends])) return 2;

    return (mysqli_query($con, "UPDATE scheduletemplates SET name = '$sname', begins = $sbegins, ends = $sends WHERE id = $sid LIMIT 1") ? 0 : 1);
  }

  public static function _remove($id, $table, $tableDays, $fieldSchedule) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);
    $stableDays = preg_replace("/[^A-Za-z0-9 ]/", '', $tableDays);
    $sfieldSchedule = preg_replace("/[^A-Za-z0-9 ]/", '', $fieldSchedule);

    return (mysqli_query($con, "DELETE FROM $stable WHERE id = $sid LIMIT 1") && mysqli_query($con, "DELETE FROM $stableDays WHERE $fieldSchedule = $sid"));
  }

  public static function removeTemplate($id) {
    return self::_remove($id, "scheduletemplates", "scheduletemplatesdays", "template");
  }

  private static function _exists($id, $table) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    $query = mysqli_query($con, "SELECT id FROM $stable WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }

  public static function templateExists($id) {
    return self::_exists($id, "scheduletemplates");
  }

  public static function checkAddDayGeneric($begins, $ends, $beginsb, $endsb, $beginsl, $endsl) {
    global $con;

    $times = [];
    $times["work"] = [$begins, $ends];
    $times["breakfast"] = [$beginsb, $endsb];
    $times["lunch"] = [$beginsl, $endsl];

    foreach ($times as $time) {
      if (intervals::wellFormed($time) === false) return 1;
    }

    if (intervals::measure($times["work"]) == 0) return 4;

    if ((!intervals::isSubset($times["breakfast"], $times["work"]) && intervals::measure($times["breakfast"]) != 0) || (!intervals::isSubset($times["lunch"], $times["work"]) && intervals::measure($times["lunch"]) != 0)) return 2;

    if (intervals::overlaps($times["breakfast"], $times["lunch"]) && intervals::measure($times["breakfast"]) != 0 && intervals::measure($times["lunch"]) != 0) return 3;

    return 0;
  }

  private static function _checkAddDayParticular($id, $dow, $typeday, $table, $fieldSchedule) {
    global $con;

    $sid = (int)$id;
    $sdow = (int)$dow;
    $stypeday = (int)$typeday;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);
    $sfieldSchedule = preg_replace("/[^A-Za-z0-9 ]/", '', $fieldSchedule);

    $query = mysqli_query($con, "SELECT id FROM $stable WHERE $fieldSchedule = $sid AND day = $sdow AND typeday = $stypeday");

    return (!mysqli_num_rows($query));
  }

  public static function checkAddDay2TemplateParticular($id, $dow, $typeday) {
    return self::_checkAddDayParticular($id, $dow, $typeday, "scheduletemplatesdays", "template");
  }

  private static function _addDay($id, $dow, $typeday, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, $table, $fieldSchedule) {
    global $con;

    $sid = (int)$id;
    $sdow = (int)$dow;
    $stypeday = (int)$typeday;
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    $sbeginsb = (int)$beginsb;
    $sendsb = (int)$endsb;
    $sbeginsl = (int)$beginsl;
    $sendsl = (int)$endsl;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);
    $sfieldSchedule = preg_replace("/[^A-Za-z0-9 ]/", '', $fieldSchedule);

    return mysqli_query($con, "INSERT INTO $stable ($sfieldSchedule, day, typeday, beginswork, endswork, beginsbreakfast, endsbreakfast, beginslunch, endslunch) VALUES ($sid, $sdow, $stypeday, $sbegins, $sends, $sbeginsb, $sendsb, $sbeginsl, $sendsl)");
  }

  public static function addDay2Template($id, $dow, $typeday, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl) {
    return self::_addDay($id, $dow, $typeday, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, "scheduletemplatesdays", "template");
  }

  public static function _getDay($id, $table) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    $query = mysqli_query($con, "SELECT * FROM $stable WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function getTemplateDay($id) {
    return self::_getDay($id, "scheduletemplatesdays");
    global $con;
  }

  private static function _editDay($id, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, $table) {
    global $con;

    $sid = (int)$id;
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    $sbeginsb = (int)$beginsb;
    $sendsb = (int)$endsb;
    $sbeginsl = (int)$beginsl;
    $sendsl = (int)$endsl;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    return mysqli_query($con, "UPDATE $stable SET beginswork = $sbegins, endswork = $sends, beginsbreakfast = $sbeginsb, endsbreakfast = $sendsb, beginslunch = $sbeginsl, endslunch = $sendsl WHERE id = $sid LIMIT 1");
  }

  public static function editTemplateDay($id, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl) {
    return self::_editDay($id, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, "scheduletemplatesdays");
  }

  private static function _removeDay($id, $table) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    return mysqli_query($con, "DELETE FROM $stable WHERE id = $sid LIMIT 1");
  }

  public static function removeTemplateDay($id) {
    return self::_removeDay($id, "scheduletemplatesdays");
  }

  private static function _dayExists($id, $table) {
    global $con;

    $sid = (int)$id;
    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    $query = mysqli_query($con, "SELECT id FROM $stable WHERE id = $sid");

    return (mysqli_num_rows($query) > 0);
  }

  public static function templateDayExists($id) {
    return self::_dayExists($id, "scheduletemplatesdays");
  }

  // SCHEDULES:

  public static function checkOverlap($worker, $begins, $ends, $sans = 0) {
    global $con;

    $sworker = (int)$worker;
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    $ssans = (int)$sans;

    $query = mysqli_query($con, "SELECT * FROM schedules WHERE begins <= $sends AND ends >= $sbegins AND worker = $sworker".($sans == 0 ? "" : " AND id <> $ssans")." LIMIT 1");

    return (mysqli_num_rows($query) > 0);
  }

  public static function get($id) {
    return self::_get($id, "schedules", "schedulesdays", "schedule");
  }

  public static function getAll($id, $showNotActive = true) {
    global $con, $conf;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM schedules WHERE worker = $sid".($showNotActive ? "" : " AND active = 1")." ORDER BY ".($conf["debug"] ? "id ASC" : "begins DESC"));

    $schedules = [];

    while ($row = mysqli_fetch_assoc($query)) {
      $schedules[] = $row;
    }

    return $schedules;
  }

  public static function getCurrent($id = "ME", $isWorker = false) {
    global $con;

    if ($id == "ME") $id = people::userData("id");
    $sid = (int)$id;

    $date = new DateTime(date("Y-m-d")."T00:00:00");
    $timestamp = (int)$date->getTimestamp();

    $query = mysqli_query($con, "SELECT s.* FROM schedules s ".($isWorker ? "WHERE s.worker = $sid" : "LEFT JOIN workers w ON s.worker = w.id WHERE w.person = $sid")." AND s.active = 1 AND s.begins <= $timestamp AND s.ends >= $timestamp");

    $return = [];

    while ($row = mysqli_fetch_assoc($query)) {
      self::_addDaysToReturn($row, "schedulesdays", "schedule", $row["id"]);
      $return[] = $row;
    }

    return $return;
  }

  public static function getWorkerScheduleStatus($id) {
    $currentSchedules = self::getCurrent($id, true);
    if ($currentSchedules === false || !count($currentSchedules)) return ["status" => self::STATUS_NO_ACTIVE_SCHEDULE];

    $schedule =& $currentSchedules[0];

    foreach (calendars::$workingTypes as $type) {
      if (!isset($schedule["days"][$type]) || !count($schedule["days"][$type])) return ["status" => self::STATUS_HALFWAY_CONFIGURED_SCHEDULE, "schedule" => $schedule["id"]];
    }

    return ["status" => self::STATUS_ACTIVE_SCHEDULE, "schedule" => $schedule["id"]];
  }

  public static function add($worker, $ibegins, $iends, $active = 0, $alreadyTimestamp = false) {
    global $con;

    $sworker = (int)$worker;
    $sactive = (int)$active;
    if ($alreadyTimestamp) {
      $sbegins = (int)$ibegins;
      $sends = (int)$iends;
    } else {
      $begins = new DateTime($ibegins);
      $sbegins = (int)$begins->getTimestamp();
      $ends = new DateTime($iends);
      $sends = (int)$ends->getTimestamp();
    }

    if (!intervals::wellFormed([$sbegins, $sends])) return 3;

    if (self::checkOverlap($worker, $sbegins, $sends)) {
      return 1;
    }

    return (mysqli_query($con, "INSERT INTO schedules (worker, begins, ends, active) VALUES ('$sworker', $sbegins, $sends, $sactive)") ? 0 : 2);
  }

  public static function edit($id, $ibegins, $iends) {
    global $con;

    $sid = (int)$id;
    $begins = new DateTime($ibegins);
    $sbegins = (int)$begins->getTimestamp();
    $ends = new DateTime($iends);
    $sends = (int)$ends->getTimestamp();

    if (!intervals::wellFormed([$sbegins, $sends])) return 3;

    $actual = self::get($sid);
    if ($actual === false) return 4;

    if (self::checkOverlap($actual["worker"], $sbegins, $sends, $sid)) {
      return 1;
    }

    return (mysqli_query($con, "UPDATE schedules SET begins = $sbegins, ends = $sends WHERE id = $sid LIMIT 1") ? 0 : 2);
  }

  public static function remove($id) {
    return self::_remove($id, "schedules", "schedulesdays", "schedule");
  }

  public static function switchActive($id, $value) {
    global $con;

    $sid = (int)$id;
    $svalue = (int)$value;
    if ($svalue > 1 || $svalue < 0) return false;

    return mysqli_query($con, "UPDATE schedules SET active = $svalue WHERE id = $sid LIMIT 1");
  }

  public static function exists($id) {
    return self::_exists($id, "schedules");
  }

  public static function checkAddDay2ScheduleParticular($id, $dow, $typeday) {
    return self::_checkAddDayParticular($id, $dow, $typeday, "schedulesdays", "schedule");
  }

  public static function addDay2Schedule($id, $dow, $typeday, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl) {
    return self::_addDay($id, $dow, $typeday, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, "schedulesdays", "schedule");
  }

  public static function getDay($id) {
    return self::_getDay($id, "schedulesdays");
    global $con;
  }

  public static function editDay($id, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl) {
    return self::_editDay($id, $begins, $ends, $beginsb, $endsb, $beginsl, $endsl, "schedulesdays");
  }

  public static function dayExists($id) {
    return self::_dayExists($id, "schedulesdays");
  }

  public static function removeDay($id) {
    return self::_removeDay($id, "schedulesdays");
  }

  public static function copyTemplate($template, $worker, $active) {
    global $con;

    $template = self::getTemplate($template);
    if ($template === false) return 1;

    $status = self::add($worker, $template["begins"], $template["ends"], $active, true);
    if ($status != 0) return ($status + 1);

    $id = mysqli_insert_id($con);

    foreach ($template["days"] as $typeday) {
      foreach ($typeday as $day) {
        $status2 = self::addDay2Schedule($id, $day["day"], $day["typeday"], $day["beginswork"], $day["endswork"], $day["beginsbreakfast"], $day["endsbreakfast"], $day["beginslunch"], $day["endslunch"]);
        if (!$status2) return -1;
      }
    }

    return 0;
  }
}
