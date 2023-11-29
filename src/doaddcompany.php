<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["name", security::PARAM_NEMPTY],
  ["cif", security::PARAM_ISSET]
])) {
  security::go("companies.php?msg=empty");
}

$name = $_POST["name"];
$cif = $_POST["cif"];

if (companies::add($name, $cif)) {
  security::go("companies.php?msg=added");
} else {
  security::go("companies.php?msg=unexpected");
}
