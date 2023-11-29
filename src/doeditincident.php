<?php
require_once("core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

$isAdmin = security::isAdminView();
$defaultUrl = ($isAdmin ? "incidents.php" : "userincidents.php?id=".$_SESSION["id"]);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT],
  ["type", security::PARAM_ISINT],
  ["day", security::PARAM_ISDATE]
])) {
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}

$id = (int)$_POST["id"];
$type = (int)$_POST["type"];
$day = $_POST["day"];

$incident = incidents::get($id, true);
if ($incident === false) security::go(visual::getContinueUrl($url, "unexpected", "POST"));

$istatus = incidents::getStatus($incident);

if (($isAdmin && in_array($istatus, incidents::$cannotEditStates)) || (!$isAdmin && !in_array($istatus, incidents::$workerCanEditStates))) security::notFound();
if (!$isAdmin) incidents::checkIncidentIsFromPerson($incident["id"]);

if (isset($_POST["allday"]) && $_POST["allday"] == 1) {
  $begins = 0;
  $ends = incidents::ENDOFDAY;
} else {
  if (!security::checkParams("POST", [
    ["begins", security::PARAM_ISTIME],
    ["ends", security::PARAM_ISTIME]
  ])) {
    security::go(visual::getContinueUrl($url, "empty", "POST"));
  }

  $begins = schedules::time2sec($_POST["begins"]);
  $ends = schedules::time2sec($_POST["ends"]);
}

$status = incidents::edit($id, $type, $day, $begins, $ends);

switch ($status) {
  case 0:
  security::go(visual::getContinueUrl($url, "modified", "POST"));
  break;

  case 2:
  security::go(visual::getContinueUrl($url, "overlap", "POST"));
  break;

  case 3:
  security::go(visual::getContinueUrl($url, "order", "POST"));
  break;

  default:
  security::go(visual::getContinueUrl($url, "unexpected", "POST"));
}
