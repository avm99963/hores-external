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
security::checkType(security::WORKER);

$url = (security::isAllowed(security::ADMIN) ? "incidents.php" : "userincidents.php?id=".(int)$_SESSION["id"]);

if (!security::checkParams("POST", [
  ["type", security::PARAM_ISINT],
  ["worker", security::PARAM_ISINT],
  ["day", security::PARAM_ISDATE]
])) {
  security::go(visual::getContinueUrl($url, "empty", "POST"));
}

$type = (int)$_POST["type"];
$worker = (int)$_POST["worker"];
$day = $_POST["day"];
$details = ((isset($_POST["details"]) && is_string($_POST["details"])) ? $_POST["details"] : "");

if (isset($_POST["allday"]) && $_POST["allday"] == 1) {
  $begins = incidents::STARTOFDAY;
  $ends = incidents::ENDOFDAY;
} else {
  if (!security::checkParams("POST", [
    ["begins", security::PARAM_ISTIME],
    ["ends", security::PARAM_ISTIME]
  ])) {
    security::go(visual::getContinueUrl($url, "empty", "POST"));
  }

  $begins = schedules::time2sec($_POST["begins"]);
  $ends = schedules::time2sec($_POST["ends"]);
}

$verified = ((isset($_POST["autoverify"]) && $_POST["autoverify"] == 1 && security::isAllowed(security::ADMIN)) ? 1 : 0);
$isAdminView = security::isAdminView();

$incidentPerson = people::workerData("id", $worker);

$status = incidents::add($worker, $type, $details, $day, $begins, $ends, "ME", $verified, false, !$isAdminView, true, ($incidentPerson == people::userData("id")));

switch ($status) {
  case 0:
  security::go(visual::getContinueUrl($url, "added", "POST"));
  break;

  case 2:
  security::go(visual::getContinueUrl($url, "overlap", "POST"));
  break;

  case 3:
  security::go(visual::getContinueUrl($url, "order", "POST"));
  break;

  case 4:
  security::go(visual::getContinueUrl($url, "addedemailnotsent", "POST"));
  break;

  case 5:
  security::go(visual::getContinueUrl($url, "addednotautovalidated", "POST"));
  break;

  default:
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}
