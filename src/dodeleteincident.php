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

$isAdmin = security::isAdminView();
$url = ($isAdmin ? "incidents.php?" : "userincidents.php?id=".$_SESSION["id"]."&");

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT]
])) {
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}

$id = (int)$_POST["id"];

$incident = incidents::get($id, true);
if ($incident === false) security::go(visual::getContinueUrl($url, "unexpected", "POST"));

$istatus = incidents::getStatus($incident);

if (($isAdmin && !in_array($istatus, incidents::$canRemoveStates)) || (!$isAdmin && !in_array($istatus, incidents::$workerCanRemoveStates))) security::notFound();
if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);

if (incidents::remove($id)) {
  security::go(visual::getContinueUrl($url, "removed", "POST"));
} else {
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}
