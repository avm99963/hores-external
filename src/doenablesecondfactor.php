<?php
require_once("core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (secondFactor::isEnabled()) {
  security::notFound();
}

if (!security::checkParams("POST", [
  ["secret", security::PARAM_ISSET],
  ["code", security::PARAM_ISINT]
])) {
  security::go("security.php?msg=empty");
}

$secret = (string)$_POST["secret"];
$code = (string)$_POST["code"];

if (!secondFactor::checkCode($secret, $code)) {
  security::go("security.php?msg=wrongcode");
}

if (secondFactor::enable($secret)) {
  security::go("security.php?msg=enabledsecondfactor");
} else {
  security::go("security.php?msg=unexpected");
}
