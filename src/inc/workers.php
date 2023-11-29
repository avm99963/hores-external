<?php
class workers {
  const AFFILIATION_STATUS_NOTWORKING = 0;
  const AFFILIATION_STATUS_WORKING = 1;
  const AFFILIATION_STATUS_AUTO_NOTWORKING = 2;
  const AFFILIATION_STATUS_AUTO_WORKING = 3;

  public static $affiliationStatuses = [self::AFFILIATION_STATUS_NOTWORKING, self::AFFILIATION_STATUS_WORKING, self::AFFILIATION_STATUS_AUTO_NOTWORKING, self::AFFILIATION_STATUS_AUTO_WORKING];
  public static $affiliationStatusesNotWorking = [self::AFFILIATION_STATUS_NOTWORKING, self::AFFILIATION_STATUS_AUTO_NOTWORKING];
  public static $affiliationStatusesWorking = [self::AFFILIATION_STATUS_WORKING, self::AFFILIATION_STATUS_AUTO_WORKING];
  public static $affiliationStatusesAutomatic = [self::AFFILIATION_STATUS_AUTO_WORKING, self::AFFILIATION_STATUS_AUTO_NOTWORKING];
  public static $affiliationStatusesManual = [self::AFFILIATION_STATUS_WORKING, self::AFFILIATION_STATUS_NOTWORKING];

  private static $return = "w.id id, w.person person, w.company company, c.name companyname, p.name name, p.dni dni";

  public static function get($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT ".self::$return."
      FROM workers w
      LEFT JOIN companies c ON w.company = c.id
      LEFT JOIN people p ON w.person = p.id
      WHERE w.id = $sid");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function sqlAddonToGetStatusAttribute($id) {
    $sid = (int)$id;

    return "LEFT JOIN workhistory h ON w.id = h.worker
    WHERE
      w.person = $sid AND
      (
        (
          SELECT COUNT(*)
          FROM workhistory
          WHERE worker = w.id AND day <= UNIX_TIMESTAMP()
          LIMIT 1
        ) = 0 OR

        h.id = (
          SELECT id
          FROM workhistory
          WHERE worker = w.id AND day <= UNIX_TIMESTAMP()
          ORDER BY day DESC
          LIMIT 1
        )
      )";
  }

  public static function getPersonWorkers($person, $simplify = false) {
    global $con;

    $query = mysqli_query($con, "SELECT ".($simplify ? "w.id id" : self::$return).", h.status status, h.day lastupdated
      FROM workers w
      LEFT JOIN companies c ON w.company = c.id
      LEFT JOIN people p ON w.person = p.id
      ".self::sqlAddonToGetStatusAttribute($person)."
      ORDER BY
        w.id ASC");

    $results = [];

    while ($row = mysqli_fetch_assoc($query)) {
      $row["hidden"] = self::isHidden($row["status"]);
      $results[] = ($simplify ? $row["id"] : $row);
    }

    return $results;
  }

  public static function isHidden($status) {
    return (in_array($status, self::$affiliationStatusesWorking) ? 0 : 1);
  }

  public static function affiliationStatusHelper($status) {
    return (self::isHidden($status) ? "Baja" : "Alta");
  }

  public static function affiliationStatusIcon($status) {
    return (self::isHidden($status) ? "work_off" : "work");
  }

  public static function isAutomaticAffiliation($status) {
    return in_array($status, self::$affiliationStatusesAutomatic);
  }

  public static function getWorkHistory($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM workhistory WHERE worker = $sid ORDER BY day DESC");
    if ($query === false) return false;

    $items = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $items[] = $row;
    }

    return $items;
  }

  public static function getWorkHistoryItem($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM workhistory WHERE id = $sid");
    if ($query === false || !mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function addWorkHistoryItem($id, $day, $status, $internal = false) {
    global $con;

    $sid = (int)$id;
    $stime = (int)$day;
    $sstatus = (int)$status;

    if ((!$internal && !in_array($sstatus, self::$affiliationStatusesManual)) || ($internal && !in_array($affiliationStatuses))) return false;

    if (!workers::exists($sid)) return false;

    return mysqli_query($con, "INSERT INTO workhistory (worker, day, status) VALUES ($sid, $stime, $sstatus)");
  }

  public static function editWorkHistoryItem($id, $day, $status, $internal = false) {
    global $con;

    $sid = (int)$id;
    $stime = (int)$day;
    $sstatus = (int)$status;

    if ((!$internal && !in_array($sstatus, self::$affiliationStatusesManual)) || ($internal && !in_array($affiliationStatuses))) return false;

    if (!self::existsWorkHistoryItem($id)) return false;

    return mysqli_query($con, "UPDATE workhistory SET day = $stime, status = $sstatus WHERE id = $sid LIMIT 1");
  }

  public static function deleteWorkHistoryItem($id) {
    global $con;

    $sid = (int)$id;

    return mysqli_query($con, "DELETE FROM workhistory WHERE id = $sid LIMIT 1");
  }

  public static function exists($id) {
    global $con;

    $query = mysqli_query($con, "SELECT id FROM workers WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }

  public static function existsWorkHistoryItem($id) {
    global $con;

    $query = mysqli_query($con, "SELECT 1 FROM workhistory WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }
}
