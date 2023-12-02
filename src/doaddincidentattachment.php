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

$isAdmin = security::isAllowed(security::ADMIN);
$url = ($isAdmin ? "incidents.php" : "userincidents.php?id=".$_SESSION["id"]);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT]
])) {
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}

if (!isset($_FILES["file"]) || $_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE) {
  security::go(visual::getContinueUrl($url, "empty", "POST"));
}

$id = (int)$_POST["id"];

$incident = incidents::get($id, true);
if ($incident === false) security::go(visual::getContinueUrl($url, "unexpected", "POST"));

$status = incidents::getStatus($incident);

if (in_array($status, incidents::$cannotEditCommentsStates)) security::notFound();
if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);

$status = incidents::addAttachment($id, $_FILES["file"]);

switch ($status) {
  case 0:
  security::go(visual::getContinueUrl($url, "attachmentadded", "POST"));
  break;

  case 2:
  security::go(visual::getContinueUrl($url, "filesize", "POST"));
  break;

  case 3:
  security::go(visual::getContinueUrl($url, "filetype", "POST"));
  break;

  default:
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}
