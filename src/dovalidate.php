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
security::checkType(security::WORKER);

if (!security::checkParams("POST", [
  ["incidents", security::PARAM_ISSET],
  ["records", security::PARAM_ISSET],
  ["method", security::PARAM_ISINT]
])) {
  security::go("validations.php?msg=unexpected");
}

$method = (int)$_POST["method"];

$status = validations::validate($method, $_POST["incidents"], $_POST["records"]);
switch ($status) {
  case 0:
  security::go("validations.php?msg=success");
  break;

  case 1:
  security::go("validations.php?msg=partialsuccess");
  break;

  default:
  security::go("validations.php?msg=unexpected");
}
