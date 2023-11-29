<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

if (!security::checkParams("POST", [
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE],
  ["workers", security::PARAM_ISARRAY]
])) {
  security::go("invalidatebulkrecords.php?msg=empty");
}

$begins = $_POST["begins"];
$ends = $_POST["ends"];

if (!intervals::wellFormed([$begins, $ends])) {
  security::go("invalidatebulkrecords.php?msg=inverted");
}

$flag = true;

foreach ($_POST["workers"] as $workerid) {
  if (!registry::invalidateAll($workerid, $begins, $ends)) $flag = false;
}

security::go("invalidatebulkrecords.php?msg=".($flag ? "success" : "partialortotalfailure"));
