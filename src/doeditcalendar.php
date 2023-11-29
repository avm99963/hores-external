<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["type", security::PARAM_ISSET]
])) {
  security::go("calendars.php?msg=unexpected");
}

$id = (int)$_POST["id"];

$c = calendars::get($id);

if ($c === false) {
  security::go("calendars.php?msg=unexpected");
}

$calendar_response = calendars::parseFormCalendar($_POST["type"], $c["begins"], $c["ends"], true);

if ($calendar_response === false) {
  security::go("calendars.php?msg=unexpected");
}

if (calendars::edit($id, $calendar_response["calendar"])) {
  security::go("calendars.php?msg=modified");
} else {
  security::go("calendars.php?msg=unexpected");
}
