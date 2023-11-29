<?php
class common {
  public static function getDayTimestamp($originaltime) {
    $datetime = new DateTime();
    $datetime->setTimestamp($originaltime);

    $rawdate = $datetime->format("Y-m-d")."T00:00:00";
    $date = new DateTime($rawdate);

    return $time = $date->getTimestamp();
  }

  public static function getTimestampFromRFC3339($rfc) {
    $date = new DateTime($rfc);
    return (int)$date->getTimestamp();
  }
}
