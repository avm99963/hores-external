<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["value", security::PARAM_ISINT]
])) {
  security::go("users.php?msg=unexpected");
}

$id = (int)$_POST["id"];
$value = (int)$_POST["value"];

if (schedules::switchActive($id, $value)) {
  security::go("schedule.php?id=".(int)$id."&msg=activeswitched".$value);
} else {
  security::go("schedule.php?id=".(int)$id."&msg=unexpected");
}
