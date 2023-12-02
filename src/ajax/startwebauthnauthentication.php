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

require_once(__DIR__."/../core.php");

if (!secondFactor::isAvailable() || security::userType() !== security::UNKNOWN || !isset($_SESSION["firstfactorid"]) || !secondFactor::isEnabled($_SESSION["firstfactorid"]) || !secondFactor::hasSecurityKeys($_SESSION["firstfactorid"]) || $_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error();
}

try {
  $result = secondFactor::createValidationChallenge();
} catch (Throwable $e) {
  api::error($conf['debug'] ? $e->getMessage() : null);
}

if (isset($result->publicKey)) $result->publicKey->rpId = ($conf["secondFactor"]["origin"] ?? null);

api::write($result);
