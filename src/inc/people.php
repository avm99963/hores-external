<?php
class people {
  public static $filters = ["categories", "types", "companies"];
  public static $mysqlFilters = ["categories", "types"];
  public static $mysqlFiltersFields = ["p.category", "p.type"];

  public static function add($username, $name, $dni, $email, $category, $password_hash, $type) {
    global $con;

    $susername = db::sanitize($username);
    $sname = db::sanitize($name);
    $sdni = db::sanitize($dni);
    $semail = db::sanitize($email);
    $scategory = (int)$category;
    $spassword_hash = db::sanitize($password_hash);
    $stype = (int)$type;

    if (!categories::exists($category) || !security::existsType($type)) return false;

    return mysqli_query($con, "INSERT INTO people (username, name, dni, email, category, password, type) VALUES ('$susername', '$sname', '$sdni', '$semail', $scategory, '$spassword_hash', $stype)");
  }

  public static function edit($id, $username, $name, $dni, $email, $category, $type) {
    global $con;

    $sid = (int)$id;
    $susername = db::sanitize($username);
    $sname = db::sanitize($name);
    $sdni = db::sanitize($dni);
    $semail = db::sanitize($email);
    $scategory = (int)$category;
    $stype = (int)$type;

    return mysqli_query($con, "UPDATE people SET username = '$susername', name = '$sname', dni = '$sdni', email = '$semail', category = $scategory, type = $stype WHERE id = $sid LIMIT 1");
  }

  public static function updatePassword($id, $password_hash) {
    global $con;

    $sid = (int)$id;
    $spassword_hash = db::sanitize($password_hash);

    return mysqli_query($con, "UPDATE people SET password = '$spassword_hash' WHERE id = $sid LIMIT 1");
  }

  public static function workerViewChangePassword($oldpassword, $newpassword) {
    global $_SESSION;

    if (!security::isUserPassword(false, $oldpassword)) return false;

    return self::updatePassword($_SESSION["id"], password_hash($newpassword, PASSWORD_DEFAULT));
  }

  private static function addCompaniesToRow(&$row, $isWorker = false, $showHiddenCompanies = true) {
    global $con;

    $query = mysqli_query($con, "SELECT w.id id, w.company company, h.status status
      FROM workers w ".workers::sqlAddonToGetStatusAttribute($row["id"]));

    $row["baixa"] = true;
    if ($isWorker) $row["hidden"] = true;
    $row["companies"] = [];
    while ($row2 = mysqli_fetch_assoc($query)) {
      $baixa = workers::isHidden($row2["status"]);

      if ($isWorker && $row2["id"] == $row["workerid"]) $row["hidden"] = $baixa;
      if (!$baixa) $row["baixa"] = false;
      if (!$showHiddenCompanies && $baixa) continue;
      $row["companies"][$row2["id"]] = $row2["company"];
    }
  }

  private static function filterCompanies($fc, $pc) { // Filter Companies, Person Companies
    foreach ($pc as $c) {
      if (in_array($c, $fc)) {
        return true;
      }
    }
    return false;
  }

  public static function get($id, $showHiddenCompanies = true) {
    global $con;

    $query = mysqli_query($con, "SELECT p.id id, p.username username, p.type type, p.name name, p.dni dni, p.email email, p.category categoryid, c.name category FROM people p LEFT JOIN categories c ON p.category = c.id WHERE p.id = ".(int)$id);

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);
    self::addCompaniesToRow($row, false, $showHiddenCompanies);

    return $row;
  }

  public static function getAll($select = false, $treatCompaniesSeparated = false) {
    global $con, $conf;

    $mysqlSelect = false;
    if ($select !== false) {
      $mysqlSelect = true;
      $flag = false;
      foreach (self::$mysqlFilters as $f) {
        if ($select["enabled"][$f]) {
          $flag = true;
          break;
        }
      }

      if (!$flag) {
        $mysqlSelect = false;
      }
    }

    if ($mysqlSelect !== false) {
      $categoryChilds = categories::getChildren();
      $where = " WHERE ";
      $conditions = [];
      foreach (self::$mysqlFilters as $i => $f) {
        if ($select["enabled"][$f]) {
          $insideconditions = [];
          foreach ($select["selected"][$f] as $value) {
            $insideconditions[] = self::$mysqlFiltersFields[$i]." = ".(int)$value;
            if ($f == "categories" && isset($categoryChilds[(int)$value])) {
              foreach ($categoryChilds[(int)$value] as $child) {
                $insideconditions[] = self::$mysqlFiltersFields[$i]." = ".(int)$child;
              }
            }
          }
          $conditions[] = "(".implode(" OR ", $insideconditions).")";
        }
      }
      $where .= implode(" AND ", $conditions);
    } else {
      $where = "";
    }

    $query = mysqli_query($con, "SELECT
      p.id id,
      p.username username,
      p.type type,
      p.name name,
      p.dni dni,
      p.email email,
      p.category categoryid,
      c.name category
      ".($treatCompaniesSeparated ? ", w.id workerid, w.company companyid" : "")."
    FROM people p
    LEFT JOIN categories c
      ON p.category = c.id
    ".($treatCompaniesSeparated ? " RIGHT JOIN workers w
      ON p.id = w.person" : "").$where);

    $people = [];

    while ($row = mysqli_fetch_assoc($query)) {
      self::addCompaniesToRow($row, $treatCompaniesSeparated);

      if ($select === false || !$select["enabled"]["companies"] || (!$treatCompaniesSeparated && self::filterCompanies($select["selected"]["companies"], $row["companies"]) || ($treatCompaniesSeparated && in_array($row["companyid"], $select["selected"]["companies"])))) {
        $people[] = $row;
      }
    }

    // Order people by name and baixa
    if ($treatCompaniesSeparated) {
      usort($people, function($a, $b) {
        if ($a["hidden"] == 0 && $b["hidden"] == 1) return -1;
        if ($a["hidden"] == 1 && $b["hidden"] == 0) return 1;
        return ($a["name"] < $b["name"] ? -1 : ($a["name"] == $b["name"] ? 0 : 1));
      });
    } else {
      usort($people, function($a, $b) {
        if ($a["baixa"] == 0 && $b["baixa"] == 1) return -1;
        if ($a["baixa"] == 1 && $b["baixa"] == 0) return 1;
        return ($a["name"] < $b["name"] ? -1 : ($a["name"] == $b["name"] ? 0 : 1));
      });
    }

    return $people;
  }

  public static function exists($id) {
    global $con;

    $query = mysqli_query($con, "SELECT id FROM people WHERE id = ".(int)$id);

    return (mysqli_num_rows($query) > 0);
  }

  public static function addToCompany($id, $company) {
    global $con;

    $sid = (int)$id;
    $scompany = (int)$company;

    if (!companies::exists($scompany)) return false;
    if (!people::exists($sid)) return false;

    $query = mysqli_query($con, "SELECT id FROM workers WHERE person = $sid AND company = $scompany");
    if (mysqli_num_rows($query)) return false;

    $time = (int)time();

    if (!mysqli_query($con, "INSERT INTO workers (person, company) VALUES ($sid, $scompany)")) return false;

    $sworkerId = (int)mysqli_insert_id($con);
    $stime = (int)time();

    return mysqli_query($con, "INSERT INTO workhistory (worker, day, status) VALUES ($sworkerId, $stime, ".(int)workers::AFFILIATION_STATUS_AUTO_WORKING.")");
  }

  public static function userData($data, $id = "ME") {
    global $con, $_SESSION;

    if ($id == "ME" && $data == "id") return $_SESSION["id"];
    if ($id == "ME") $id = $_SESSION["id"];
    $sdata = preg_replace("/[^A-Za-z0-9 ]/", '', $data);
    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT $sdata FROM people WHERE id = $sid");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return $row[$sdata];
  }

  public static function workerData($data, $id) {
    global $con, $_SESSION;

    $sdata = preg_replace("/[^A-Za-z0-9 ]/", '', $data);
    $sid = (int)$id;

    $query = mysqli_query($con, "SELECT p.$sdata $sdata FROM people p INNER JOIN workers w ON p.id = w.person WHERE w.id = $sid");

    if (!mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return $row[$sdata];
  }
}
