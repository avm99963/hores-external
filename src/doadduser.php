<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["username", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY],
  ["dni", security::PARAM_ISSET],
  ["email", security::PARAM_ISEMAILOREMPTY],
  ["category", security::PARAM_NEMPTY],
  ["password", security::PARAM_NEMPTY],
  ["type", security::PARAM_ISSET]
])) {
  security::go("users.php?msg=empty");
}

if (!security::passwordIsGoodEnough($_POST["password"])) security::go("users.php?msg=weakpassword");

$username = $_POST["username"];
$name = $_POST["name"];
$dni = $_POST["dni"];
$email = $_POST["email"];
$category = (int)$_POST["category"];
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
$type = (int)$_POST["type"];

if (!security::isAllowed($type)) security::go("users.php?msg=unexpected");

if (people::add($username, $name, $dni, $email, $category, $password_hash, $type)) {
  security::go("users.php?msg=added");
} else {
  security::go("users.php?msg=unexpected");
}
