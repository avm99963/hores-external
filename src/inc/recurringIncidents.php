<?php
class recurringIncidents {
  /*public static function oldAdd($worker, $type, $details, $ifirstday, $ilastday, $begins, $ends, $creator = "ME", $typedays, $days, $alreadyTimestamp = false) {
    global $con, $conf;

    $sworker = (int)$worker;
    $workerDetails = workers::get($sworker);
    if ($workerDetails === false) return 1;

    if ($creator === "ME") $creator = people::userData("id");
    $screator = (int)$creator;

    if (!security::isAllowed(security::ADMIN) && $workerDetails["person"] != $creator) return 5;

    $stype = (int)$type;
    $sverified = (int)$verified;
    $sdetails = db::sanitize($details);

    $incidenttype = self::getType($stype);
    if ($incidenttype === false) return -1;

    if ($alreadyTimestamp) {
      $sfirstday = (int)$ifirstday;
      $slastday = (int)$ilastday;
    } else {
      $firstday = new DateTime($ifirstday);
      $sfirstday = (int)$firstday->getTimestamp();
      $lastday = new DateTime($ilastday);
      $slastday = (int)$lastday->getTimestamp();
    }

    if ($sfirstday >= $slastday) return 3;

    $sbegins = (int)$begins;
    $sends = (int)$ends;
    if ($sbegins >= $sends) return 3;

    $typedays = array_unique(array_map(function($el) {
      return (int)$el;
    }, $typedays));
    foreach ($typedays as $typeday) {
      if (!in_array($typeday, $workingTypes)) return 6;
    }
    $stypedays = json_encode($typedays);

    if (!mysqli_query($con, "INSERT INTO recurringincidents (worker, creator, type, firstday, lastday, typedays, begins, ends, details) VALUES ($sworker, $screator, $stype, $sfirstday, $slastday, '$stypedays', $sbegins, $sends, '$sdetails')")) return -1;

    return 0;
  }*/ // NOTE: This was a first idea, to allow setting up recurring incidents like schedules, but we've changed how we'll handle them and so this is no longer useful.

  public static function add($worker, $type, $details, $ifirstday, $ilastday, $begins, $ends, $creator = "ME", $typeDays, $days, $alreadyTimestamp = false) {
    if ($alreadyTimestamp) {
      $current = new DateTime();
      $current->setTimestamp($ifirstday);
      $lastday = new DateTime();
      $lastday->setTimestamp($ilastday);
    } else {
      $current = new DateTime($ifirstday);
      $lastday = new DateTime($ilastday);
    }

    $oneDay = new DateInterval("P1D");

    $category = registry::getWorkerCategory($worker);
    if ($category === false) return false;

    $flag = true;

    for (; $current->diff($lastday)->invert === 0; $current->add($oneDay)) {
      $currentTimestamp = $current->getTimestamp();
      $currentDay = (int)$current->format("N") - 1;

      if (!in_array($currentDay, $days)) continue;

      $calendarDays = registry::getDayTypes($currentTimestamp);
      if ($calendarDays === false) return false;

      if (isset($calendarDays[$category])) {
        $typeDay = $calendarDays[$category];
      } else if (isset($calendarDays[-1])) {
        $typeDay = $calendarDays[-1];
      } else {
        $flag = false;
        continue;
      }

      if (!in_array($typeDay, $typeDays)) continue;

      if ($status = incidents::add($worker, $type, $details, $currentTimestamp, $begins, $ends, $creator, 1, true, false, false) !== 0) {
        $flag = false;
        continue;
      }
    }

    return $flag;
  }
}
