<?php
require_once("core.php");
security::checkType(security::WORKER);

if (!security::checkParams("POST", [
  ["incidents", security::PARAM_ISSET],
  ["records", security::PARAM_ISSET],
  ["method", security::PARAM_ISINT]
])) {
  security::go("validations.php?msg=unexpected");
}

$method = (int)$_POST["method"];

$status = validations::validate($method, $_POST["incidents"], $_POST["records"]);
switch ($status) {
  case 0:
  security::go("validations.php?msg=success");
  break;

  case 1:
  security::go("validations.php?msg=partialsuccess");
  break;

  default:
  security::go("validations.php?msg=unexpected");
}
