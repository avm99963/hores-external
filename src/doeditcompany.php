<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY],
  ["cif", security::PARAM_ISSET]
])) {
  security::go("companies.php?msg=empty");
}

$id = (int)$_POST["id"];
$name = $_POST["name"];
$cif = $_POST["cif"];

if (companies::edit($id, $name, $cif)) {
  security::go("companies.php?msg=modified");
} else {
  security::go("companies.php?msg=unexpected");
}
