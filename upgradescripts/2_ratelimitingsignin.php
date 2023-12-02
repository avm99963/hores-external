<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

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
