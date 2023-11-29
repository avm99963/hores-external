<?php
require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "====================\n";
echo "generateregistry.php\n";
echo "====================\n\n";

if (!isset($argc)) {
  echo "[error] An unexpected error occurred (\$argc is not set).\n";
  exit();
}

$time = ($argc > 1 ? (int)$argv[1] : time());

$logId = -1;
registry::generateNow($time, $logId, false);

echo "[info] Log ID: ".$logId."\n";
