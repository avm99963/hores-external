<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("scheduletemplates.php?msg=unexpected");
}

$id = (int)$_POST["id"];

if (!schedules::templateExists($id)) {
  security::go("scheduletemplates.php?msg=unexpected");
}

if (schedules::removeTemplate($id)) {
  security::go("scheduletemplates.php?msg=deleted");
} else {
  security::go("scheduletemplates.php?msg=unexpected");
}
