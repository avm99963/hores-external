<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["type", security::PARAM_ISSET],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go("calendars.php?msg=unexpected");
}

$id = (int)$_POST["id"];

if (!categories::exists($id)) {
  security::go("calendars.php?msg=unexpected");
}

$calendar_response = calendars::parseFormCalendar($_POST["type"], $_POST["begins"], $_POST["ends"]);

if ($calendar_response === false) {
  security::go("calendars.php?msg=unexpected");
}

$return = calendars::add($id, $calendar_response["begins"], $calendar_response["ends"], $calendar_response["calendar"]);

switch ($return) {
  case 0:
  security::go("calendars.php?msg=added");

  case -1:
  security::go("calendars.php?msg=overlap");

  default:
  security::go("calendars.php?msg=unexpected");
}
