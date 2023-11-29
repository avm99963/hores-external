<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY],
  ["emails", security::PARAM_ISSET],
  ["parent", security::PARAM_ISSET]
])) {
  security::go("categories.php?msg=empty");
}

$id = (int)$_POST["id"];
$name = $_POST["name"];
$emails = $_POST["emails"];
$parent = (int)$_POST["parent"];

if (categories::edit($id, $name, $emails, $parent)) {
  security::go("categories.php?msg=modified");
} else {
  security::go("categories.php?msg=unexpected");
}
