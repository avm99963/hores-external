<?php
require_once(__DIR__."/../core.php");

if (!secondFactor::isAvailable() || security::userType() !== security::UNKNOWN || !isset($_SESSION["firstfactorid"]) || !secondFactor::isEnabled($_SESSION["firstfactorid"])) {
  api::error();
}

$input = api::inputJson();
if ($input === false || !isset($input["code"])) api::error();

$code = (string)$input["code"];

if (secondFactor::completeCodeChallenge($code)) {
  api::write(["status" => "ok"]);
} else {
  api::write(["status" => "wrongCode"]);
}
