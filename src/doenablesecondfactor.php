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

if (secondFactor::isEnabled()) {
  security::notFound();
}

if (!security::checkParams("POST", [
  ["secret", security::PARAM_ISSET],
  ["code", security::PARAM_ISINT]
])) {
  security::go("security.php?msg=empty");
}

$secret = (string)$_POST["secret"];
$code = (string)$_POST["code"];

if (!secondFactor::checkCode($secret, $code)) {
  security::go("security.php?msg=wrongcode");
}

if (secondFactor::enable($secret)) {
  security::go("security.php?msg=enabledsecondfactor");
} else {
  security::go("security.php?msg=unexpected");
}
