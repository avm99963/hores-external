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

// @description: Program used to upgrade the database after the implementation of issue #23.

require_once(__DIR__."/../core.php");

if (php_sapi_name() != "cli") {
  security::notFound();
  exit();
}

echo "=========================\n";
echo "upgradebaixes2history.php\n";
echo "=========================\n\n";

// Check whether the database was already upgraded
$prequery = mysqli_query($con, "SHOW COLUMNS FROM workers LIKE 'hidden'");
if (!mysqli_num_rows($prequery)) {
  die("[fatal error] The database was already upgraded.\n");
}

echo "[info] Adding new database schema...\n";
if (!mysqli_query($con, "CREATE TABLE workhistory (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT NOT NULL,
  INDEX(worker),
  day INT NOT NULL,
  status INT NOT NULL
)")) die("[fatal error] Couldn't add new database.\n");

echo "[info] Transfering affiliation information from the workers table to the workhistory table...\n";
$query = mysqli_query($con, "SELECT * FROM workers");

if ($query === false) exit();

while ($worker = mysqli_fetch_assoc($query)) {
  $sid = (int)$worker["id"];
  $sday = (int)$worker["lastupdated"];
  $sstatus = (int)($worker["hidden"] == "1" ? (workers::AFFILIATION_STATUS_AUTO_NOTWORKING) : (workers::AFFILIATION_STATUS_AUTO_WORKING));
  $sql = "INSERT INTO workhistory (worker, day, status) VALUES ($sid, $sday, $sstatus)";
  if (!mysqli_query($con, $sql)) {
    echo "[error] Failed to upgrade ".$worker["id"]."\n";
  }
}

echo "[info] Removing the 'hidden' and 'lastupdated' columns from the 'workers' table, and the 'baixa' column from the 'people' table...\n";
if (!mysqli_query($con, "ALTER TABLE workers DROP hidden")) echo "[error] Failed to remove 'hidden' column from 'workers' table.\n";
if (!mysqli_query($con, "ALTER TABLE workers DROP lastupdated")) echo "[error] Failed to remove 'lastupdated' column from 'workers' table.\n";
if (!mysqli_query($con, "ALTER TABLE people DROP baixa")) echo "[error] Failed to remove 'baixa' column from 'people' table.\n";

echo "[info] Done\n";
