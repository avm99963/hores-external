<?php
require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["id", security::PARAM_NEMPTY],
  ["username", security::PARAM_NEMPTY],
  ["name", security::PARAM_NEMPTY],
  ["dni", security::PARAM_ISSET],
  ["email", security::PARAM_ISEMAILOREMPTY],
  ["category", security::PARAM_NEMPTY],
  ["type", security::PARAM_ISSET]
])) {
  security::go("users.php?msg=empty");
}

$id = (int)$_POST["id"];
$username = $_POST["username"];
$name = $_POST["name"];
$dni = $_POST["dni"];
$email = $_POST["email"];
$category = (int)$_POST["category"];
$type = (int)$_POST["type"];

$p = people::get($id);
if ($p === false) security::go("users.php?msg=unexpected");

if (!security::isAllowed($type) || !security::isAllowed($p["type"]) || !categories::exists($category) || !security::existsType($type)) security::go("users.php?msg=unexpected");

if (people::edit($id, $username, $name, $dni, $email, $category, $type)) {
  if (security::checkParams("POST", [["password", security::PARAM_NEMPTY]])) {
    if (!security::passwordIsGoodEnough($_POST["password"])) security::go("users.php?msg=weakpassword");

    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    if (!people::updatePassword($id, $password_hash)) {
      security::go("users.php?msg=couldntupdatepassword");
    }
  }
} else {
  security::go("users.php?msg=unexpected");
}

security::go("users.php?msg=modified");
