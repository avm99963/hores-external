<?php
require_once("core.php");
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

if (!security::checkParams("POST", [
  ["oldpassword", security::PARAM_NEMPTY],
  ["newpassword", security::PARAM_NEMPTY]
])) {
  security::go("users.php?msg=empty");
}

$oldpassword = $_POST["oldpassword"];
$newpassword = $_POST["newpassword"];

if (people::workerViewChangePassword($oldpassword, $newpassword)) {
  header("Location: workerhome.php?msg=passwordchanged");
} else {
  header("Location: changepassword.php?msg=wrong");
}
