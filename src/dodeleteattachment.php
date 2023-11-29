<?php
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
