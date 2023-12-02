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
  ["id", security::PARAM_ISINT],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go((isset($_POST["id"]) ? "schedule.php?id=".(int)$_POST["id"]."msg=empty" : "users.php"));
}

$id = $_POST["id"];
$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::edit($id, $begins, $ends);
switch ($status) {
  case 0:
  security::go("schedule.php?id=".(int)$id."&msg=modified");
  break;

  case 1:
  security::go("schedule.php?id=".(int)$id."&msg=overlaps");
  break;

  case 3:
  security::go("schedule.php?id=".(int)$id."&msg=order");
  break;

  default:
  security::go("schedule.php?id=".(int)$id."&msg=unexpected");
  break;
}
