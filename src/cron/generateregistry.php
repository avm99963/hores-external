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
