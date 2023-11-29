<?php
// @description: Program used to upgrade the database to fix a bug which didn't let multiple users have an empty DNI.

require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "=====================\n";
echo "3_peoplednibugfix.php\n";
echo "=====================\n\n";

echo "[info] Removing unique index for field dni in table people...\n";
if (!mysqli_query($con, "ALTER TABLE people DROP INDEX dni")) die("[fatal error] Couldn't delete index: ".mysqli_error($con)."\n");

echo "[info] Done\n";
