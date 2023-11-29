<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("scheduletemplates.php?msg=unexpected");
}

$id = (int)$_POST["id"];

if (!security::checkParams("POST", [
  ["day", security::PARAM_ISARRAY],
  ["type", security::PARAM_ISARRAY]
])) {
  security::go("scheduletemplate.php?id=".$id."&msg=empty");
}

$dates = ["beginswork", "endswork", "beginsbreakfast", "endsbreakfast", "beginslunch", "endslunch"];
$time = [];
foreach ($dates as $date) {
  if (isset($_POST[$date]) && !empty($_POST[$date])) {
    if (!security::checkParam($_POST[$date], security::PARAM_ISTIME)) {
      security::go("scheduletemplate.php?id=".$id."&msg=unexpected");
    }
    $time[$date] = schedules::time2sec($_POST[$date]);
  } else {
    $time[$date] = 0;
  }
}

$status = schedules::checkAddDayGeneric($time["beginswork"], $time["endswork"], $time["beginsbreakfast"], $time["endsbreakfast"], $time["beginslunch"], $time["endslunch"]);

if ($status != 0) {
  security::go("scheduletemplate.php?id=".$id."&msg=errorcheck".(int)$status);
}

$flag = false;

foreach ($_POST["day"] as $rawday) {
  $day = (int)$rawday;
  if ($day < 0 || $day > 6) continue;

  foreach ($_POST["type"] as $rawtype) {
    $type = (int)$rawtype;
    if (!in_array($type, array_keys(calendars::$types))) continue;

    if (!schedules::checkAddDay2TemplateParticular($id, $day, $type)) {
      $flag = true;
      continue;
    }

    if (!schedules::addDay2Template($id, $day, $type, $time["beginswork"], $time["endswork"], $time["beginsbreakfast"], $time["endsbreakfast"], $time["beginslunch"], $time["endslunch"])) {
      security::go("scheduletemplate.php?id=".$id."&msg=unexpected");
    }
  }
}

if ($flag) {
  security::go("scheduletemplate.php?id=".$id."&msg=existing");
} else {
  security::go("scheduletemplate.php?id=".$id."&msg=added");
}
