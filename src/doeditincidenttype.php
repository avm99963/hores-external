<?php
require_once("core.php");
security::checkType(security::ADMIN);


if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY]
])) {
  security::go("incidenttypes.php?msg=empty");
}

$id = (int)$_POST["id"];
$name = $_POST["name"];
$present = ((isset($_POST["present"]) && $_POST["present"] == 1) ? 1 : 0);
$paid = ((isset($_POST["paid"]) && $_POST["paid"] == 1) ? 1 : 0);
$workerfill = ((isset($_POST["workerfill"]) && $_POST["workerfill"] == 1) ? 1 : 0);
$notifies = ((isset($_POST["notifies"]) && $_POST["notifies"] == 1) ? 1 : 0);
$autovalidates = ((isset($_POST["autovalidates"]) && $_POST["autovalidates"] == 1) ? 1 : 0);
$hidden = ((isset($_POST["hidden"]) && $_POST["hidden"] == 1) ? 1 : 0);

if (incidents::editType($id, $name, $present, $paid, $workerfill, $notifies, $autovalidates, $hidden)) {
  security::go("incidenttypes.php?msg=modified");
} else {
  security::go("incidenttypes.php?msg=unexpected");
}
