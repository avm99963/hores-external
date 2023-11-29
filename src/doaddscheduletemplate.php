<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["name", security::PARAM_NEMPTY],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go("scheduletemplates.php?msg=empty");
}

$name = $_POST["name"];
$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::addTemplate($name, $begins, $ends);
switch ($status) {
  case 0:
  $id = mysqli_insert_id($con);
  security::go("scheduletemplate.php?id=".(int)$id."&msg=added");
  break;

  case 2:
  security::go("scheduletemplates.php?msg=order");
  break;

  default:
  security::go("scheduletemplates.php?msg=unexpected");
}
