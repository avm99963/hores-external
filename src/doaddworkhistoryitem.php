<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["day", security::PARAM_ISSET],
  ["status", security::PARAM_ISSET]
])) {
  security::go("workers.php?msg=empty");
}

$id = $_POST["id"];
$date = new DateTime($_POST["day"]);
$day = $date->getTimestamp();
$status = $_POST["status"];

if (workers::addWorkHistoryItem($id, $day, $status)) security::go("workers.php?openWorkerHistory=".(int)$id);
else security::go("workers.php?msg=unexpected");
