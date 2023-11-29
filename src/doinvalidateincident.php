<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT]
])) {
  security::go(visual::getContinueUrl("incidents.php", "unexpected", "POST"));
}

$id = (int)$_POST["id"];

if (incidents::invalidate($id)) {
  security::go(visual::getContinueUrl("incidents.php", "invalidated", "POST"));
} else {
  security::go(visual::getContinueUrl("incidents.php", "unexpected", "POST"));
}
