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
  ["username", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY],
  ["dni", security::PARAM_ISSET],
  ["email", security::PARAM_ISEMAILOREMPTY],
  ["category", security::PARAM_NEMPTY],
  ["password", security::PARAM_NEMPTY],
  ["type", security::PARAM_ISSET]
])) {
  security::go("users.php?msg=empty");
}

if (!security::passwordIsGoodEnough($_POST["password"])) security::go("users.php?msg=weakpassword");

$username = $_POST["username"];
$name = $_POST["name"];
$dni = $_POST["dni"];
$email = $_POST["email"];
$category = (int)$_POST["category"];
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
$type = (int)$_POST["type"];

if (!security::isAllowed($type)) security::go("users.php?msg=unexpected");

if (people::add($username, $name, $dni, $email, $category, $password_hash, $type)) {
  security::go("users.php?msg=added");
} else {
  security::go("users.php?msg=unexpected");
}
