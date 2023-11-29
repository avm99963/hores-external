<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["incidents", security::PARAM_ISARRAY]
])) {
  security::go("incidents.php?msg=empty");
}

$allOk = true;
foreach ($_POST["incidents"] as $id) {
  $incident = incidents::get($id, true);
  if ($incident === false) security::go($returnURL."msg=unexpected");

  $istatus = incidents::getStatus($incident);

  if (in_array($istatus, incidents::$canRemoveStates)) {
    if (!incidents::remove($id)) $allOk = false;
  } elseif (in_array($istatus, incidents::$canInvalidateStates)) {
    if (!incidents::invalidate($id)) $allOk = false;
  } else $allOk = false;
}

security::go("incidents.php?msg=deleteincidentsbulk".($allOk ? "success" : "partialsuccess"));
