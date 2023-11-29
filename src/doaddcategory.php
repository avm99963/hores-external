<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["name", security::PARAM_NEMPTY],
  ["emails", security::PARAM_ISSET],
  ["parent", security::PARAM_ISSET]
])) {
  security::go("categories.php?msg=empty");
}

$name = $_POST["name"];
$emails = $_POST["emails"];
$parent = (int)$_POST["parent"];

if (categories::add($name, $emails, $parent)) {
  security::go("categories.php?msg=added");
} else {
  security::go("categories.php?msg=unexpected");
}
