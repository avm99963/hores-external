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

$item = workers::getWorkHistoryItem($id);
if ($item === false) return false;

if (workers::editWorkHistoryItem($id, $day, $status)) security::go("workers.php?openWorkerHistory=".(int)$item["worker"]);
else security::go("workers.php?msg=unexpected");
