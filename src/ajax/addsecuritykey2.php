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
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
secondFactor::checkAvailability();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error('This method should be called with POST.');
}

$input = api::inputJson();
if ($input === false || !isset($input["clientDataJSON"]) || !isset($input["attestationObject"]) || !isset($input["name"])) api::error();
$clientDataJSON = (string)$input["clientDataJSON"];
$attestationObject = (string)$input["attestationObject"];
$name = (string)$input["name"];

try {
  $result = secondFactor::completeRegistrationChallenge($clientDataJSON, $attestationObject, $name);
} catch (Throwable $e) {
  api::error('An unexpected error occurred: ' . $e->getMessage());
}

api::write($result);
