<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

$advancedMode = (isset($_POST["advanced"]) && $_POST["advanced"] == "1");

if (!$advancedMode) {
  if (!security::checkParams("POST", [
    ["day", security::PARAM_ISDATE],
    ["workers", security::PARAM_ISARRAY]
  ])) {
    security::go("manuallygenerateregistry.php?msg=empty");
  }

  $day = new DateTime($_POST["day"]);
  $time = $day->getTimestamp();

  $logId = -1;
  $status = registry::generateNow($time, $logId, true, people::userData("id"), $_POST["workers"]);

  security::go("manuallygenerateregistry.php?".($logId == -1 ? "msg=generatederr" : "")."&logId=".$logId);
} else {
  if (!security::checkParams("POST", [
    ["begins", security::PARAM_ISDATE],
    ["ends", security::PARAM_ISDATE],
    ["workers", security::PARAM_ISARRAY]
  ])) {
    security::go("manuallygenerateregistry.php?msg=empty");
  }

  $executedBy = people::userData("id");

  $current = new DateTime($_POST["begins"]);
  $ends = new DateTime($_POST["ends"]);
  $interval = new DateInterval("P1D");
  while ($current->diff($ends)->invert === 0) {
    $logId = 0;
    registry::generateNow($current->getTimestamp(), $logId, true, $executedBy, $_POST["workers"]);

    $current->add($interval);
  }

  security::go("manuallygenerateregistry.php?msg=done");
}
