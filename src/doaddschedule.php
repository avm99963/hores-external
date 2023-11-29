<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["worker", security::PARAM_NEMPTY]
])) {
  security::go("users.php?msg=unexpected");
}

$w = workers::get((int)$_POST["worker"]);

if ($w === false) {
  security::go("users.php?msg=unexpected");
}

if (!security::checkParams("POST", [
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=empty");
}

$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::add($w["id"], $begins, $ends);

switch ($status) {
  case 0:
  $id = mysqli_insert_id($con);
  security::go("schedule.php?id=".(int)$id."&msg=added");
  break;

  case 1:
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=overlaps");
  break;

  case 3:
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=order");
  break;

  default:
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=unexpected");
  break;
}
