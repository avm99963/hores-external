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

$returnURL = (security::isAdminView() ? "incidents.php?" : "userincidents.php?id=".$_SESSION["id"]."&");

if (!security::checkParams("GET", [
  ["id", security::PARAM_ISINT],
  ["name", security::PARAM_NEMPTY]
])) {
  security::go($returnURL."msg=unexpected");
}

$id = (int)$_GET["id"];
$name = $_GET["name"];

$incident = incidents::get($id, true);
if ($incident === false) security::go($returnURL."msg=unexpected");

if (!security::isAllowed(security::ADMIN)) incidents::checkIncidentIsFromPerson($incident["id"]);

$attachments = incidents::getAttachmentsFromIncident($incident);

if ($attachments === false || !count($attachments)) security::go($returnURL."msg=unexpected");

$flag = false;

foreach ($attachments as $attachment) {
  if ($attachment == $name) {
    $flag = true;

    $fullpath = $conf["attachmentsFolder"].$attachment;
    $extension = files::getFileExtension($attachment);

    if (!isset(files::$mimeTypes[$extension])) {
      exit();
    }

    header("Content-type: ".(files::$mimeTypes[$extension] ?? "application/octet-stream"));
    header("Content-Disposition: filename=\"".$attachment."\"");
    header("Content-Length: ".filesize($fullpath));
    header("Cache-control: private");
    readfile($fullpath);

    break;
  }
}

if ($flag === false) security::go($returnURL."msg=unexpected");
