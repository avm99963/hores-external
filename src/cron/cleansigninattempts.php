<?php
require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "=======================\n";
echo "cleansigninattempts.php\n";
echo "=======================\n\n";

if (security::cleanSignInAttempts()) {
  echo "[info] The action was performed successfully.\n";
} else {
  echo "[error] An error occurred.\n";
}
