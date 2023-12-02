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

class db {
  const EXPORT_DB_FORMAT_SQL = 0;

  public static function sanitize($string) {
    global $con;
    return mysqli_real_escape_string($con, $string);
  }

  public static function needsSetUp() {
    global $con;

    $checkquery = mysqli_query($con, "SELECT 1 FROM people LIMIT 1");

    return ($checkquery === false);
  }

  public static function numRows($table) {
    global $con;

    $stable = preg_replace("/[^A-Za-z0-9 ]/", '', $table);

    $query = mysqli_query($con, "SELECT 1 FROM $stable");

    if ($query === false) return -1;

    return mysqli_num_rows($query);
  }

  public static function limitPagination($start, $limit) {
    $slimit = (int)$limit;
    $sstart = $slimit*(int)$start;
    if ($slimit > 100 || $slimit < 0) return false;
    if ($sstart < 0) return false;

    return ($slimit == 0 ? "" : " LIMIT $sstart,$slimit");
  }
}
