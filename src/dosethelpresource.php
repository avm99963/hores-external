<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["place", security::PARAM_ISINT],
  ["url", security::PARAM_ISSET]
])) {
  security::go("help.php?msg=empty");
}

$status = help::set($_POST["place"], $_POST["url"]);
switch ($status) {
  case 0:
  security::go("help.php?msg=success");
  break;

  case 1:
  security::go("help.php?msg=invalidurl");
  break;

  default:
  security::go("help.php?msg=unexpected");
}
