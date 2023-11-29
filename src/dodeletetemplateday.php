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

if (schedules::removeTemplateDay($id)) {
  security::go("scheduletemplate.php?id=".(int)$day["template"]."&msg=deleted");
} else {
  security::go("scheduletemplate.php?id=".(int)$day["template"]."&msg=unexpected");
}
