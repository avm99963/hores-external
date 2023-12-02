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

class companies {
  public static function add($company, $cif) {
    global $con;

    $scompany = db::sanitize($company);
    $scif = db::sanitize($cif);
    return mysqli_query($con, "INSERT INTO companies (name, cif) VALUES ('$scompany', '$scif')");
  }

  public static function edit($id, $name, $cif) {
    global $con;

    $sid = (int)$id;
    $sname = db::sanitize($name);
    $scif = db::sanitize($cif);

    return mysqli_query($con, "UPDATE companies SET name = '$sname', cif = '$scif' WHERE id = $sid LIMIT 1");
  }

  public static function get($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM companies WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function getAll($simplified = true, $mixed = false) {
    global $con;

    $query = mysqli_query($con, "SELECT * FROM companies ORDER BY id ASC");

    $categories = [];

    while ($row = mysqli_fetch_assoc($query)) {
      if ($simplified) $categories[$row["id"]] = $row["name"];
      elseif ($mixed) $categories[$row["id"]] = $row;
      else $categories[] = $row;
    }

    return $categories;
  }

  public static function exists($id) {
    global $con;

    if ($id == -1) return true;

    $query = mysqli_query($con, "SELECT id FROM companies WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }
}
