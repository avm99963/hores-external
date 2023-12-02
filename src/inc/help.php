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

class help {
  const PLACE_INCIDENT_FORM = 0;
  const PLACE_VALIDATION_PAGE = 1;
  const PLACE_REGISTRY_PAGE = 2;
  const PLACE_EXPORT_REGISTRY_PAGE = 3;

  public static $places = [0, 1, 2, 3];
  public static $placesName = [
    0 => "Formulario de incidencias",
    1 => "Página de validación",
    2 => "Página de listado de registros",
    3 => "Página de exportar registro"
  ];

  public static function exists($place) {
    global $con;

    $splace = (int)$place;

    $query = mysqli_query($con, "SELECT 1 FROM help WHERE place = $splace");

    return (mysqli_num_rows($query) > 0);
  }

  public static function set($place, $url) {
    global $con;

    if (!in_array($place, self::$places)) return -1;
    if ($url !== "" && !filter_var($url, FILTER_VALIDATE_URL)) return 1;

    $splace = (int)$place;
    $surl = db::sanitize($url);

    if (self::exists($place)) return (mysqli_query($con, "UPDATE help SET url = '$surl' WHERE place = $splace LIMIT 1") ? 0 : -1);
    else return (mysqli_query($con, "INSERT INTO help (place, url) VALUES ('$splace', '$surl')") ? 0 : -1);
  }

  public static function get($place) {
    global $con;

    if (!in_array($place, self::$places)) return false;
    $splace = (int)$place;

    $query = mysqli_query($con, "SELECT url FROM help WHERE place = $splace");

    if (mysqli_num_rows($query) > 0) {
      $url = mysqli_fetch_assoc($query)["url"];
      return ($url === "" ? false : $url);
    } else return false;
  }
}
