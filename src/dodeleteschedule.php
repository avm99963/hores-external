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
