<?php
// @description: Program used to upgrade the database after the implementation of issue #39.

require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "========================\n";
echo "2_ratelimitingsignin.php\n";
echo "========================\n\n";

echo "[info] Adding new database schema...\n";
if (!mysqli_query($con, "CREATE TABLE signinattempts (
  username VARCHAR(100) NOT NULL,
  KEY username (username),
  remoteip VARBINARY(16) NOT NULL,
  KEY remoteip (remoteip),
  remoteipblock VARBINARY(16) NOT NULL,
  KEY remoteipblock (remoteipblock),
  signinattempttime DATETIME NOT NULL,
  KEY signinattempttime (signinattempttime)
)")) die("[fatal error] Couldn't add new database: ".mysqli_error($con)."\n");

echo "[info] Done\n";
