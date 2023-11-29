<?php
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
    return self::measure(self::intersect($a, $b));
  }
}
