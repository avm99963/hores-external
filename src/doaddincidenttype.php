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
  ["name", security::PARAM_NEMPTY]
])) {
  security::go("incidenttypes.php?msg=empty");
}

$name = $_POST["name"];
$present = ((isset($_POST["present"]) && $_POST["present"] == 1) ? 1 : 0);
$paid = ((isset($_POST["paid"]) && $_POST["paid"] == 1) ? 1 : 0);
$workerfill = ((isset($_POST["workerfill"]) && $_POST["workerfill"] == 1) ? 1 : 0);
$notifies = ((isset($_POST["notifies"]) && $_POST["notifies"] == 1) ? 1 : 0);
$autovalidates = ((isset($_POST["autovalidates"]) && $_POST["autovalidates"] == 1) ? 1 : 0);
$hidden = ((isset($_POST["hidden"]) && $_POST["hidden"] == 1) ? 1 : 0);

if (incidents::addType($name, $present, $paid, $workerfill, $notifies, $autovalidates, $hidden)) {
  security::go("incidenttypes.php?msg=added");
} else {
  security::go("incidenttypes.php?msg=unexpected");
}
