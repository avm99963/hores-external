<?php
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
