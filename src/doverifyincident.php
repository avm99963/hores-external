<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_ISINT],
  ["value", security::PARAM_ISINT]
])) {
  security::go(visual::getContinueUrl("incidents.php", "unexpected", "POST"));
}

$id = (int)$_POST["id"];
$value = ($_POST["value"] == 1 ? 1 : 0);

if (incidents::verify($id, $value)) {
  security::go(visual::getContinueUrl("incidents.php", "verified".$value, "POST"));
} else {
  security::go(visual::getContinueUrl("incidents.php", "unexpected", "POST"));
}
