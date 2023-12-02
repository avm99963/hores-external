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

if (!security::checkParams("POST", [
  ["type", security::PARAM_ISINT],
  ["worker", security::PARAM_ISINT],
  ["firstday", security::PARAM_ISDATE],
  ["lastday", security::PARAM_ISDATE],
  ["day", security::PARAM_ISARRAY],
  ["daytype", security::PARAM_ISARRAY]
])) {
  security::go(visual::getContinueUrl("incidents.php", "empty", "POST"));
}

$type = (int)$_POST["type"];
$worker = (int)$_POST["worker"];
$details = ((isset($_POST["details"]) && is_string($_POST["details"])) ? $_POST["details"] : "");
$firstday = $_POST["firstday"];
$lastday = $_POST["lastday"];
$days = $_POST["day"];
$typeDays = $_POST["daytype"];

if (isset($_POST["allday"]) && $_POST["allday"] == 1) {
  $begins = incidents::STARTOFDAY;
  $ends = incidents::ENDOFDAY;
} else {
  if (!security::checkParams("POST", [
    ["begins", security::PARAM_ISTIME],
    ["ends", security::PARAM_ISTIME]
  ])) {
    security::go(visual::getContinueUrl("incidents.php", "empty", "POST"));
  }

  $begins = schedules::time2sec($_POST["begins"]);
  $ends = schedules::time2sec($_POST["ends"]);
}

if (recurringIncidents::add($worker, $type, $details, $firstday, $lastday, $begins, $ends, "ME", $typeDays, $days)) security::go(visual::getContinueUrl("incidents.php", "addedrecurring", "POST"));
else security::go(visual::getContinueUrl("incidents.php", "unexpectedrecurring", "POST"));
