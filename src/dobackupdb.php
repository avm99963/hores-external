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

require_once("core.php");
security::checkType(security::HYPERADMIN);

if (!security::checkParams("POST", [
  ["format", security::PARAM_ISINT]
])) security::notFound();

switch ($_POST["format"]) {
  default:
  header("Content-Type: application/sql");
  header("Content-Disposition: filename=\"registrohorario_backup_".(int)date("Ymd").".sql\"");
  header("Cache-control: private");
  passthru("mysqldump ".escapeshellarg($conf["db"]["database"])." --host=".escapeshellarg($conf["db"]["host"])." --user=".escapeshellarg($conf["db"]["user"])." --password=".escapeshellarg($conf["db"]["password"]));
}
