<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("calendars.php?msg=unexpected");
}

$id = (int)$_POST["id"];

if (!calendars::exists($id)) {
  security::go("calendars.php?msg=unexpected");
}

if (calendars::remove($id)) {
  security::go("calendars.php?msg=deleted");
} else {
  security::go("calendars.php?msg=unexpected");
}
