<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT],
  ["name", security::PARAM_NEMPTY],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go((isset($_POST["id"]) ? "scheduletemplate.php?id=".(int)$_POST["id"]."msg=empty" : "scheduletemplates.php?msg=empty"));
}

$id = $_POST["id"];
$name = $_POST["name"];
$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::editTemplate($id, $name, $begins, $ends);
switch ($status) {
  case 0:
  security::go("scheduletemplate.php?id=".(int)$id."&msg=modified");
  break;

  case 2:
  security::go("scheduletemplate.php?id=".(int)$id."&msg=order");
  break;

  default:
  security::go("scheduletemplate.php?id=".(int)$id."&msg=unexpected");
}
