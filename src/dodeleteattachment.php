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

$url = (security::isAllowed(security::ADMIN) ? "incidents.php" : "userincidents.php?id=".(int)$_SESSION["id"]);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT],
  ["name", security::PARAM_NEMPTY]
])) {
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));

}

$id = (int)$_POST["id"];
$name = $_POST["name"];

$incident = incidents::get($id, true);
if ($incident === false) security::go(visual::getContinueUrl($url, "unexpected", "POST"));

if (!security::isAllowed(security::ADMIN)) incidents::checkIncidentIsFromPerson($incident["id"]);

security::go(visual::getContinueUrl($url, (incidents::deleteAttachment($id, $name) ? "attachmentdeleted" : "unexpected"), "POST"));
