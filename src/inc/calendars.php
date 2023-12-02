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

class calendars {
  const TYPE_FESTIU = 0;
  const TYPE_FEINER = 1;
  const TYPE_LECTIU = 2;

  const NO_CALENDAR_APPLICABLE = 0;

  public static $types = array(
    0 => "Festivo",
    2 => "Lectivo",
    1 => "No lectivo"
  );
  public static $workingTypes = [1, 2];
  public static $days = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
  public static $months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

  public static function parseFormCalendar($form, $ibegins, $iends, $timestamp = false) {
    if ($timestamp) {
      $current = new DateTime();
      $current->setTimestamp((int)$ibegins);
      $ends = new DateTime();
      $ends->setTimestamp((int)$iends);
    } else {
      $current = new DateTime($ibegins);
      $ends = new DateTime($iends);
    }
    $interval = new DateInterval("P1D");

    if ($current->diff($ends)->invert === 1) {
      return false;
    }

    $return = array(
      "begins" => $current->getTimestamp(),
      "ends" => $ends->getTimestamp(),
      "calendar" => []
    );

    $possible_values = array_keys(self::$types);

    $day = 0;
    while ($current->diff($ends)->invert === 0) {
      if (!isset($form[$day]) || !in_array($form[$day], $possible_values)) {
        return false;
      }

      $return["calendar"][$current->getTimestamp()] = (int)$form[$day];

      $day++;
      $current->add($interval);
    }

    return $return;
  }

  public static function checkOverlap($category, $begins, $ends) {
    global $con;

    $scategory = (int)$category;
    $sbegins = (int)$begins;
    $sends = (int)$ends;

    $query = mysqli_query($con, "SELECT * FROM calendars WHERE begins <= $sends AND ends >= $sbegins AND category = $scategory LIMIT 1");

    return (mysqli_num_rows($query) > 0);
  }

  public static function add($category, $begins, $ends, $calendar) {
    global $con;

    if (self::checkOverlap($category, $begins, $ends)) {
      return -1;
    }

    $scategory = (int)$category;
    $sbegins = (int)$begins;
    $sends = (int)$ends;
    $scalendar = db::sanitize(json_encode($calendar));

    return (mysqli_query($con, "INSERT INTO calendars (category, begins, ends, details) VALUES ($scategory, $sbegins, $sends, '$scalendar')") ? 0 : -2);
  }

  public static function edit($id, $calendar) {
    global $con;

    $sid = (int)$id;
    $scalendar = db::sanitize(json_encode($calendar));

    return (mysqli_query($con, "UPDATE calendars SET details = '$scalendar' WHERE id = $sid LIMIT 1"));
  }

  public static function get($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT c.id id, c.begins begins, c.ends ends, c.details details, c.category category, ca.name categoryname FROM calendars c LEFT JOIN categories ca ON c.category = ca.id WHERE c.id = $sid");

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    if ($row["category"] == -1) {
      $row["categoryname"] = "Calendario por defecto";
    }

    return $row;
  }

  public static function getByCategory($id, $details = false) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT id, begins, ends".($details ? ", details" : "")." FROM calendars WHERE category = $sid");

    $calendars = [];

    while ($row = mysqli_fetch_assoc($query)) {
      $calendars[] = $row;
    }

    return $calendars;
  }

  public static function getAll() {
    $categories = categories::getAll();
    $categories[-1] = "Calendario por defecto";

    $return = [];

    foreach ($categories as $id => $category) {
      $return[] = array(
        "category" => $category,
        "categoryid" => $id,
        "calendars" => self::getByCategory($id)
      );
    }

    return $return;
  }

  public static function getCurrentCalendarByCategory($category) {
    global $con;

    $scategory = (int)$category;
    $stime = (int)time();

    $query = mysqli_query($con, "SELECT id, category, begins, ends, details FROM calendars WHERE category IN (-1, $scategory) AND begins <= $stime AND ends >= $stime");
    if ($query === false) return false;

    $calendars = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $row["details"] = json_decode($row["details"], true);
      if (json_last_error() !== JSON_ERROR_NONE) return false;

      $calendars[$row["category"]] = $row;
    }

    return $calendars[$category] ??  $calendars[-1] ?? self::NO_CALENDAR_APPLICABLE;
  }

  public static function remove($id) {
    global $con;

    $sid = (int)$id;

    return mysqli_query($con, "DELETE FROM calendars WHERE id = $sid LIMIT 1");
  }

  public static function exists($id) {
    global $con;

    $sid = (int)$id;
    $query = mysqli_query($con, "SELECT id FROM calendars WHERE id = $sid");

    return (mysqli_num_rows($query) > 0);
  }
}
