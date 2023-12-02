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

class export {
  const FORMAT_PDF = 1;
  const FORMAT_DETAILEDPDF = 2;
  const FORMAT_CSV_SCHEDULES = 3;
  const FORMAT_CSV_INCIDENTS = 4;

  public static $formats = array(
    1 => "PDF con horario laboral",
    2 => "PDF detallado",
    3 => "CSV con horarios",
    4 => "CSV con incidencias"
  );
  public static $workerFormats = [1, 3, 4];

  public static $schedulesFields = ["id", "worker", "workerid", "dni", "company", "day", "beginswork", "endswork", "beginsbreakfast", "endsbreakfast", "beginslunch", "endslunch", "state"];
  public static $incidentsFields = ["id", "worker", "workerid", "dni", "company", "creator", "updated", "updatedby", "confirmedby", "type", "day", "allday", "begins", "ends", "details", "workerdetails", "verified", "typepresent", "typepaid", "state"];

  public static function convert($str) {
    return iconv('UTF-8', 'windows-1252', $str);
  }

  public static function getDays($worker, $begins, $ends, $showvalidated, $shownotvalidated) {
    $return = [];

    $records = registry::getRecords($worker, $begins, $ends, false, false, false, 0, 0);
    if ($records === false) return false;
    foreach ($records as $record) {
      if (!$showvalidated && $record["state"] === registry::STATE_VALIDATED_BY_WORKER) continue;
      if (!$shownotvalidated && $record["state"] === registry::STATE_REGISTERED) continue;
      if (!isset($return[$record["day"]])) $return[$record["day"]] = [];
      $return[$record["day"]]["schedule"] = $record;
    }

    $incidents = incidents::getAll(false, 0, 0, $worker, $begins, $ends);
    if ($incidents === false) return false;
    foreach ($incidents as $incident) {
      if ($incident["state"] !== incidents::STATE_REGISTERED && $incident["state"] !== incidents::STATE_VALIDATED_BY_WORKER) continue;
      if (!$showvalidated && $incident["state"] === incidents::STATE_VALIDATED_BY_WORKER) continue;
      if (!$shownotvalidated && $incident["state"] === incidents::STATE_REGISTERED) continue;
      if (!isset($return[$incident["day"]])) $return[$incident["day"]] = [];
      if (!isset($return[$incident["day"]]["incidents"])) $return[$incident["day"]]["incidents"] = [];
      $return[$incident["day"]]["incidents"][] = $incident;
    }

    ksort($return);

    return $return;
  }

  public static function sec2hours($sec) {
    return round((double)$sec/3600, 2)." h";
  }
}
