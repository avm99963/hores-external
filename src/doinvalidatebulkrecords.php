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
security::checkType(security::HYPERADMIN);

if (!security::checkParams("POST", [
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE],
  ["workers", security::PARAM_ISARRAY]
])) {
  security::go("invalidatebulkrecords.php?msg=empty");
}

$begins = $_POST["begins"];
$ends = $_POST["ends"];

if (!intervals::wellFormed([$begins, $ends])) {
  security::go("invalidatebulkrecords.php?msg=inverted");
}

$flag = true;

foreach ($_POST["workers"] as $workerid) {
  if (!registry::invalidateAll($workerid, $begins, $ends)) $flag = false;
}

security::go("invalidatebulkrecords.php?msg=".($flag ? "success" : "partialortotalfailure"));
