<?php
require_once(__DIR__."/../core.php");

if (!secondFactor::isAvailable() || security::userType() !== security::UNKNOWN || !isset($_SESSION["firstfactorid"]) || !secondFactor::isEnabled($_SESSION["firstfactorid"]) || !secondFactor::hasSecurityKeys($_SESSION["firstfactorid"]) || $_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error();
}

$input = api::inputJson();
if ($input === false || !isset($input["id"]) || !isset($input["clientDataJSON"]) || !isset($input["authenticatorData"]) || !isset($input["signature"])) api::error();
$id = (string)$input["id"];
$clientDataJSON = (string)$input["clientDataJSON"];
$authenticatorData = (string)$input["authenticatorData"];
$signature = (string)$input["signature"];

try {
  $result = secondFactor::completeValidationChallenge($id, $clientDataJSON, $authenticatorData, $signature);
} catch (Throwable $e) {
  api::error($conf['debug'] ? $e->getMessage() : null);
}

api::write($result);
