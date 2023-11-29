<?php
require_once("core.php");

if (!security::checkParams("POST", [
  ["token", security::PARAM_NEMPTY],
  ["password", security::PARAM_NEMPTY]
])) {
  security::go("index.php?msg=unexpected");
}

$token = $_POST["token"];
$password = $_POST["password"];

if (!security::passwordIsGoodEnough($password)) security::go("recovery.php?token=".$token."&msg=weakpassword");

$status = recovery::finishRecovery($token, $password);

security::go("index.php?msg=".($status ? "recoverycompleted" : "recovery2failed"));
