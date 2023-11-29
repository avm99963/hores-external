<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
secondFactor::checkAvailability();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error('This method should be called with POST.');
}

$input = api::inputJson();
if ($input === false || !isset($input["clientDataJSON"]) || !isset($input["attestationObject"]) || !isset($input["name"])) api::error();
$clientDataJSON = (string)$input["clientDataJSON"];
$attestationObject = (string)$input["attestationObject"];
$name = (string)$input["name"];

try {
  $result = secondFactor::completeRegistrationChallenge($clientDataJSON, $attestationObject, $name);
} catch (Throwable $e) {
  api::error('An unexpected error occurred: ' . $e->getMessage());
}

api::write($result);
