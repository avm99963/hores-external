<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("workers.php?msg=empty");
}

$id = $_POST["id"];

$item = workers::getWorkHistoryItem($id);
if ($item === false) return false;

if (workers::deleteWorkHistoryItem($id)) security::go("workers.php?openWorkerHistory=".(int)$item["worker"]);
else security::go("workers.php?msg=unexpected");
