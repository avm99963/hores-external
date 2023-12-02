<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

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
