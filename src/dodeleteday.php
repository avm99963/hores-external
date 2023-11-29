<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("users.php?msg=unexpected");
}

$id = (int)$_POST["id"];

$day = schedules::getDay($id);

if ($day === false) {
  security::go("users.php?msg=unexpected");
}

if (schedules::removeDay($id)) {
  security::go("schedule.php?id=".(int)$day["schedule"]."&msg=deleted");
} else {
  security::go("schedule.php?id=".(int)$day["schedule"]."&msg=unexpected");
}
