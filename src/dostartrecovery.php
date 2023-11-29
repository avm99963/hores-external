<?php
require_once("core.php");

if (!$conf["enableRecovery"] || !$conf["mail"]["enabled"]) security::notFound();

if (!security::checkParams("POST", [
  ["email", security::PARAM_ISEMAIL],
  ["dni", security::PARAM_NEMPTY]
])) {
  security::go("index.php?msg=unexpected");
}

$email = $_POST["email"];
$dni = $_POST["dni"];

$user = recovery::getUser($email, $dni);
if ($user === false) {
  sleep(3);
  security::go("index.php?msg=recovery");
}

security::go("index.php?msg=".(recovery::recover($user) ? "recovery" : "unexpected"));
