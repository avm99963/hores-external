<?php
require_once(__DIR__."/../core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
secondFactor::checkAvailability();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
  api::error('This method should be called with POST.');
}

try {
  $result = secondFactor::createRegistrationChallenge();
} catch (Throwable $e) {
  api::error('An unexpected error occurred: ' . $e->getMessage());
}

api::write($result);
