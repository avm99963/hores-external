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

class intervals {
  public static function wellFormed($i) {
    return (isset($i[0]) && isset($i[1]) && $i[0] <= $i[1]);
  }

  public static function measure($i) {
    return ($i[1] - $i[0]);
  }

  // Does A overlap B? (with "$open = true" meaning [0, 100] does not overlap [100, 200])
  public static function overlaps($a, $b, $open = true) {
    return ($open ? ($a[0] < $b[1] && $a[1] > $b[0]) : ($a[0] <= $b[1] && $a[1] >= $b[0]));
  }

  // Is A inside of B?
  public static function isSubset($a, $b) {
    return ($a[0] >= $b[0] && $a[1] <= $b[1]);
  }

  // Intersect A and B and return the corresponding interval
  public static function intersect($a, $b) {
    $int = [max($a[0], $b[0]), min($a[1], $b[1])];

    return (self::wellFormed($int) ? $int : false);
  }

  // Return measure of the intersection
  public static function measureIntersection($a, $b) {
    $intersection = self::intersect($a, $b);
    if ($intersection === false) return 0;
    return self::measure($intersection);
  }
}
