<?php
require_once("core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (!secondFactor::isEnabled()) {
  security::notFound();
}

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISSET]
])) {
  security::go("security.php?msg=empty");
}

$id = (int)$_POST["id"];

$url = ((security::isAllowed(security::ADMIN) && $id != people::userData("id")) ? "users.php" : "security.php");

if (!security::isAllowed(security::ADMIN)) {
  if ($id != people::userData("id")) security::notFound();

  if (!security::checkParams("POST", [
    ["password", security::PARAM_ISSET]
  ])) {
    security::go($url."?msg=empty");
  }

  $password = (string)$_POST["password"];

  if (!security::isUserPassword(false, $password)) security::go($url."?msg=wrongpassword");
}

if (secondFactor::disable($id)) {
  security::go($url."?msg=disabledsecondfactor");
} else {
  security::go($url."?msg=unexpected");
}
