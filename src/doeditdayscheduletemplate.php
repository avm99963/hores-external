<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("scheduletemplates.php?msg=unexpected");
}

$id = (int)$_POST["id"];

$day = schedules::getTemplateDay($id);

if ($day === false) {
  security::go("scheduletemplates.php?msg=unexpected");
}

$dates = ["beginswork", "endswork", "beginsbreakfast", "endsbreakfast", "beginslunch", "endslunch"];
$time = [];
foreach ($dates as $date) {
  if (isset($_POST[$date]) && !empty($_POST[$date])) {
    if (!security::checkParam($_POST[$date], security::PARAM_ISTIME)) {
      security::go("scheduletemplate.php?id=".$day["template"]."&msg=unexpected");
    }
    $time[$date] = schedules::time2sec($_POST[$date]);
  } else {
    $time[$date] = 0;
  }
}

$status = schedules::checkAddDayGeneric($time["beginswork"], $time["endswork"], $time["beginsbreakfast"], $time["endsbreakfast"], $time["beginslunch"], $time["endslunch"]);

if ($status != 0) {
  security::go("scheduletemplate.php?id=".$day["template"]."&msg=errorcheck".(int)$status);
}

if (schedules::editTemplateDay($id, $time["beginswork"], $time["endswork"], $time["beginsbreakfast"], $time["endsbreakfast"], $time["beginslunch"], $time["endslunch"])) {
  security::go("scheduletemplate.php?id=".$day["template"]."&msg=modified");
} else {
  security::go("scheduletemplate.php?id=".$day["template"]."&msg=unexpected");
}
