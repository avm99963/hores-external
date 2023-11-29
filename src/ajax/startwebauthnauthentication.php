<?php
require_once(__DIR__."/../core.php");

if (!secondFactor::isAvailable() || security::userType() !== security::UNKNOWN || !isset($_SESSION["firstfactorid"]) || !secondFactor::isEnabled($_SESSION["firstfactorid"]) || !secondFactor::hasSecurityKeys($_SESSION["firstfactorid"]) || $_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error();
}

try {
  $result = secondFactor::createValidationChallenge();
} catch (Throwable $e) {
  api::error($conf['debug'] ? $e->getMessage() : null);
}

if (isset($result->publicKey)) $result->publicKey->rpId = ($conf["secondFactor"]["origin"] ?? null);

api::write($result);
