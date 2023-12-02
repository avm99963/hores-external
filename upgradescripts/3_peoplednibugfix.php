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
