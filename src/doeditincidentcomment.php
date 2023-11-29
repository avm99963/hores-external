<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT]
])) {
  security::go("incidents.php?msg=unexpected");
}

$id = (int)$_POST["id"];
$details = ((isset($_POST["details"]) && is_string($_POST["details"])) ? $_POST["details"] : "");

$status = incidents::editDetails($id, $details);
switch ($status) {
  case 0:
  security::go("incidents.php?msg=modified");
  break;

  case 1:
  security::go("incidents.php?msg=cannotmodify");
  break;

  default:
  security::go("incidents.php?msg=unexpected");
}
