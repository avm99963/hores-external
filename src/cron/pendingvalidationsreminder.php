<?php
require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "==============================\n";
echo "pendingvalidationsreminder.php\n";
echo "==============================\n\n";

validations::sendPendingValidationsReminder();

//echo "[info] Log ID: ".$logId."\n";
