<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("users.php?msg=unexpected");
}

$id = (int)$_POST["id"];

$s = schedules::get($id);

if ($s === false) {
  security::go("users.php?msg=unexpected");
}

$w = workers::get($s["worker"]);

if ($w === false) {
  security::go("users.php?msg=unexpected");
}

if (schedules::remove($id)) {
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=deleted");
} else {
  security::go("userschedule.php?id=".(int)$w["person"]."&msg=unexpected");
}
