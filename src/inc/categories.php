<?php
class categories {
  private static function parseEmails($string) {
    $string = str_replace(" ", "", $string);
    $emails = explode(",", $string);

    foreach ($emails as $i => &$e) {
      if (empty($e)) {
        unset($emails[$i]);
      } else {
        if (filter_var($e, FILTER_VALIDATE_EMAIL) === false) return false;
      }
    }

    return $emails;
  }

  public static function readableEmails($emails) {
    $array = json_decode($emails, true);
    if (json_last_error() != JSON_ERROR_NONE) return false;
    return implode(", ", $array);
  }

  private static function canBeParent($id) {
    if ($id == 0) return true;

    $category = self::get($id);
    return ($category !== false && $category["parent"] == 0);
  }

  public static function add($category, $stringEmails, $parent) {
    global $con;

    $emails = (empty($stringEmails) ? [] : self::parseEmails($stringEmails));
    if ($emails === false) return false;

    if (!self::canBeParent($parent)) return false;

    $scategory = db::sanitize($category);
    $semails = db::sanitize(json_encode($emails));
    $sparent = (int)$parent;

    return mysqli_query($con, "INSERT INTO categories (name, emails, parent) VALUES ('$scategory', '$semails', $sparent)");
  }

  public static function edit($id, $name, $stringEmails, $parent) {
    global $con;

    $emails = (empty($stringEmails) ? [] : self::parseEmails($stringEmails));
    if ($emails === false) return false;

    if (!self::canBeParent($parent)) return false;

    $sid = (int)$id;
    $sname = db::sanitize($name);
    $semails = db::sanitize(json_encode($emails));
    $sparent = (int)$parent;

    return mysqli_query($con, "UPDATE categories SET name = '$sname', emails = '$semails', parent = $sparent WHERE id = $sid LIMIT 1");
  }

  public static function get($id) {
    global $con;

    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT * FROM categories WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  private static function addChilds(&$row) {
    global $con;

    $query = mysqli_query($con, "SELECT * FROM categories WHERE parent = ".(int)$row["id"]);

    $row["childs"] = [];
    while ($child = mysqli_fetch_assoc($query)) {
      $row["childs"][] = $child;
    }
  }

  public static function getAll($simplified = true, $withparents = true, $includechilds = false) {
    global $con, $conf;

    $query = mysqli_query($con, "SELECT ".($simplified ? "id, name" : "c.id id, c.name name, c.parent parent, c.emails emails, p.name parentname")." FROM categories c".($simplified ? "" : " LEFT JOIN categories p ON c.parent = p.id").($withparents ? "" : " WHERE c.parent = 0")." ORDER BY ".($conf["debug"] ? "id" : "name")." ASC");

    $categories = [];

    while ($row = mysqli_fetch_assoc($query)) {
      if (!$simplified && $includechilds) self::addChilds($row);

      if ($simplified) $categories[$row["id"]] = $row["name"];
      else $categories[] = $row;
    }

    return $categories;
  }

  public static function getAllWithWorkers() {
    global $con;

    $categories = self::getAll(false);

    foreach ($categories as &$category) {
      $category["workers"] = [];

      $query = mysqli_query($con, "SELECT w.id FROM workers w LEFT JOIN people p ON w.person = p.id WHERE p.category = ".(int)$category["id"]);

      while ($row = mysqli_fetch_assoc($query)) {
        $category["workers"][] = $row["id"];
      }
    }

    return $categories;
  }

  public static function getChildren() {
    global $con, $conf;

    $query = mysqli_query($con, "SELECT p.id parent, c.id child FROM categories c LEFT JOIN categories p ON c.parent = p.id WHERE c.parent != 0");

    $childs = [];

    while ($row = mysqli_fetch_assoc($query)) {
      if (!isset($childs[$row["parent"]])) {
        $childs[$row["parent"]] = [];
      }
      $childs[$row["parent"]][] = $row["child"];
    }

    return $childs;
  }

  public static function exists($id) {
    global $con;

    if ($id == -1) return true;

    $query = mysqli_query($con, "SELECT id FROM categories WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }

  public static function getIdByName($name) {
    global $con;

    if (strtolower($name) == "sin categoría") return -1;

    $sname = db::sanitize($name);
    $query = mysqli_query($con, "SELECT id FROM categories WHERE name = '$sname'");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return $row["id"];
  }
}
