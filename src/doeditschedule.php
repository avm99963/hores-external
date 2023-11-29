<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go((isset($_POST["id"]) ? "schedule.php?id=".(int)$_POST["id"]."msg=empty" : "users.php"));
}

$id = $_POST["id"];
$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::edit($id, $begins, $ends);
switch ($status) {
  case 0:
  security::go("schedule.php?id=".(int)$id."&msg=modified");
  break;

  case 1:
  security::go("schedule.php?id=".(int)$id."&msg=overlaps");
  break;

  case 3:
  security::go("schedule.php?id=".(int)$id."&msg=order");
  break;

  default:
  security::go("schedule.php?id=".(int)$id."&msg=unexpected");
  break;
}
