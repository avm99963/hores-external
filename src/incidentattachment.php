<?php
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
