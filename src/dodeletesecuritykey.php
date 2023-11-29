<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY]
])) {
  security::go("securitykeys.php?msg=unexpected");
}

$id = (int)$_POST["id"];

$s = secondFactor::getSecurityKeyById($id);
if ($s === false || people::userData("id") != $s["person"]) security::go("securitykeys.php?msg=unexpected");

if (secondFactor::removeSecurityKey($id)) {
  security::go("securitykeys.php?msg=securitykeydeleted");
} else {
  security::go("securitykeys.php?msg=unexpected");
}
