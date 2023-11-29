<?php
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
