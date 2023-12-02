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

if (!$conf["enableRecovery"] || !$conf["mail"]["enabled"]) security::notFound();

if (!security::checkParams("POST", [
  ["email", security::PARAM_ISEMAIL],
  ["dni", security::PARAM_NEMPTY]
])) {
  security::go("index.php?msg=unexpected");
}

$email = $_POST["email"];
$dni = $_POST["dni"];

$user = recovery::getUser($email, $dni);
if ($user === false) {
  sleep(3);
  security::go("index.php?msg=recovery");
}

security::go("index.php?msg=".(recovery::recover($user) ? "recovery" : "unexpected"));
