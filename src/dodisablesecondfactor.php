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
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (!secondFactor::isEnabled()) {
  security::notFound();
}

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISSET]
])) {
  security::go("security.php?msg=empty");
}

$id = (int)$_POST["id"];

$url = ((security::isAllowed(security::ADMIN) && $id != people::userData("id")) ? "users.php" : "security.php");

if (!security::isAllowed(security::ADMIN)) {
  if ($id != people::userData("id")) security::notFound();

  if (!security::checkParams("POST", [
    ["password", security::PARAM_ISSET]
  ])) {
    security::go($url."?msg=empty");
  }

  $password = (string)$_POST["password"];

  if (!security::isUserPassword(false, $password)) security::go($url."?msg=wrongpassword");
}

if (secondFactor::disable($id)) {
  security::go($url."?msg=disabledsecondfactor");
} else {
  security::go($url."?msg=unexpected");
}
