<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!security::checkParams("POST", [
  ["person", security::PARAM_NEMPTY],
  ["company", security::PARAM_NEMPTY]
])) {
  api::error();
}

$person = (int)$_POST["person"];
$company = (int)$_POST["company"];

if (people::addToCompany($person, $company)) {
  echo "OK\n";
} else {
  api::error();
}
