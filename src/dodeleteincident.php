<?php
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
