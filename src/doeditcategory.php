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
  ["name", security::PARAM_NEMPTY],
  ["emails", security::PARAM_ISSET],
  ["parent", security::PARAM_ISSET]
])) {
  security::go("categories.php?msg=empty");
}

$id = (int)$_POST["id"];
$name = $_POST["name"];
$emails = $_POST["emails"];
$parent = (int)$_POST["parent"];

if (categories::edit($id, $name, $emails, $parent)) {
  security::go("categories.php?msg=modified");
} else {
  security::go("categories.php?msg=unexpected");
}
