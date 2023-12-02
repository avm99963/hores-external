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
  ["name", security::PARAM_NEMPTY],
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE]
])) {
  security::go("scheduletemplates.php?msg=empty");
}

$name = $_POST["name"];
$begins = $_POST["begins"];
$ends = $_POST["ends"];

$status = schedules::addTemplate($name, $begins, $ends);
switch ($status) {
  case 0:
  $id = mysqli_insert_id($con);
  security::go("scheduletemplate.php?id=".(int)$id."&msg=added");
  break;

  case 2:
  security::go("scheduletemplates.php?msg=order");
  break;

  default:
  security::go("scheduletemplates.php?msg=unexpected");
}
