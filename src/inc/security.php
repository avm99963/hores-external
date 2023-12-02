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

class security {
  const HYPERADMIN = 0;
  const ADMIN = 2;
  /*const SYSTEM_REVIEWER = 5;
  const LOG_REVIEWER = 7;*/
  const WORKER = 10;
  const UNKNOWN = 1000;

  const METHOD_UNSPECIFICED = -1;
  const METHOD_REDIRECT = 0;
  const METHOD_NOTFOUND = 1;

  const PARAM_ISSET = 1;
  const PARAM_NEMPTY = 2;
  const PARAM_ISEMAIL = 4;
  const PARAM_ISDATE = 8;
  const PARAM_ISINT = 16;
  const PARAM_ISTIME = 32;
  const PARAM_ISARRAY = 64;
  const PARAM_ISEMAILOREMPTY = 128;

  const SIGNIN_STATE_SIGNED_IN = 0;
  const SIGNIN_STATE_NEEDS_SECOND_FACTOR = 1;
  const SIGNIN_STATE_SIGNED_OUT = 2;
  const SIGNIN_STATE_THROTTLED = 3;

  public static $types = [
    10 => "Trabajador",
    /*7 => "Revisor de logs",
    5 => "Revisor del sistema",*/
    2 => "Administrador",
    0 => "Hiperadministrador"
  ];

  public static $automatedChecks = [self::PARAM_ISSET, self::PARAM_NEMPTY, self::PARAM_ISEMAIL, self::PARAM_ISDATE, self::PARAM_ISINT, self::PARAM_ISTIME, self::PARAM_ISARRAY, self::PARAM_ISEMAILOREMPTY];

  public static $passwordHelperText = "La contraseña debe tener como mínimo 8 caracteres y una letra mayúscula.";

  public static function go($page) {
    global $conf;

    if ($conf["superdebug"]) {
      echo "Redirects are not enabled. We would like to redirect you here: <a href='".self::htmlsafe($page)."'>".self::htmlsafe($page)."</a>";
    } else {
      header("Location: ".$page);
    }

    exit();
  }

  public static function goHome() {
    self::go("index.php");
  }

  public static function notFound() {
    header('HTTP/1.0 404 Not Found');
    exit();
  }

  public static function isSignedIn() {
    global $_SESSION;

    return isset($_SESSION["id"]);
  }

  public static function check() {
    if (!self::isSignedIn()) {
      self::goHome();
    }
  }

  public static function userType() {
    global $_SESSION, $con;

    if (!isset($_SESSION["id"])) {
      return self::UNKNOWN;
    }

    $query = mysqli_query($con, "SELECT type FROM people WHERE id = ".(int)$_SESSION["id"]);

    if (!mysqli_num_rows($query)) {
      return self::UNKNOWN;
    }

    $row = mysqli_fetch_assoc($query);

    return $row["type"];
  }

  public static function isAllowed($type) {
    $userType = (self::isSignedIn() ? self::userType() : self::UNKNOWN);

    return $userType <= $type;
  }

  public static function denyUseMethod($method = self::METHOD_REDIRECT) {
    if ($method === self::METHOD_NOTFOUND) {
      self::notFound();
    } else { // self::METHOD_REDIRECT or anything else
      self::goHome();
    }
  }

  public static function checkType($type, $method = self::METHOD_REDIRECT) {
    if (!self::isAllowed($type)) {
      self::denyUseMethod($method);
    }
  }

  public static function checkWorkerUIEnabled() {
    global $conf;

    if (self::userType() >= self::WORKER && !$conf["enableWorkerUI"]) {
      self::go("index.php?msg=unsupported");
    }
  }

  // Code from https://timoh6.github.io/2015/05/07/Rate-limiting-web-application-login-attempts.html
  private static function getIpAddresses() {
    $ips = [];
    $ips["remoteIp"] = inet_pton($_SERVER['REMOTE_ADDR']); // inet_pton can handle both IPv4 and IPv6 addresses, treat IPv6 addresses as /64 or /56 blocks.
    $ips["remoteIpBlock"] = long2ip(ip2long($_SERVER['REMOTE_ADDR']) & 0xFFFFFF00); // Something like this to turn the last octet of IPv4 address into a 0.
    return $ips;
  }

  public static function recordLoginAttempt($username) {
    global $con;

    $susername = db::sanitize($username);

    $ips = self::getIpAddresses();
    $sremoteIp = db::sanitize($ips["remoteIp"]);
    $sremoteIpBlock = db::sanitize($ips["remoteIpBlock"]);

    return mysqli_query($con, "INSERT INTO signinattempts (username, remoteip, remoteipblock, signinattempttime) VALUES ('$susername', '$sremoteIp', '$sremoteIpBlock', NOW())");
  }

  public static function getRateLimitingCounts($username) {
    global $con, $conf;

    $susername = db::sanitize($username);

    $ips = self::getIpAddresses();
    $sremoteIp = db::sanitize($ips["remoteIp"]);
    $sremoteIpBlock = db::sanitize($ips["remoteIpBlock"]);

    $query = mysqli_query($con, "SELECT
        COUNT(*) AS global_attempt_count,
        IFNULL(SUM(CASE WHEN remoteip = '$sremoteIp' THEN 1 ELSE 0 END), 0) AS ip_attempt_count,
        IFNULL(SUM(CASE WHEN (remoteipblock = '$sremoteIpBlock') THEN 1 ELSE 0 END), 0) AS ip_block_attempt_count,
        (SELECT COUNT(DISTINCT remoteipblock) FROM signinattempts WHERE username = '$susername' AND signinattempttime >= (NOW() - INTERVAL 10 SECOND )) AS ip_blocks_per_username_attempt_count,
        (SELECT COUNT(*) FROM signinattempts WHERE username = '$susername' AND signinattempttime >= (NOW() - INTERVAL 10 SECOND )) AS username_attempt_count
      FROM signinattempts
      WHERE signinattempttime >= (NOW() - INTERVAL 10 second)");

    if ($query === false) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function isSignInThrottled($username) {
    global $conf;

    $count = self::getRateLimitingCounts($username);

    if ($count["global_attempt_count"] >= $conf["signinThrottling"]["attemptCountLimit"]["global"] ||
        $count["ip_attempt_count"] >= $conf["signinThrottling"]["attemptCountLimit"]["ip"] ||
        $count["ip_block_attempt_count"] >= $conf["signinThrottling"]["attemptCountLimit"]["ipBlock"] ||
        $count["ip_blocks_per_username_attempt_count"] >= $conf["signinThrottling"]["attemptCountLimit"]["ipBlocksPerUsername"] ||
        $count["username_attempt_count"] >= $conf["signinThrottling"]["attemptCountLimit"]["username"]) {
      return true;
    }

    if (!self::recordLoginAttempt($username)) {
      echo "There was an unexpected error, so you could not be authenticated. Please contact me@avm99963.com and let them know of the error. (2)";
      exit();
    }

    return false;
  }

  public static function isUserPassword($username, $password) {
    global $con, $_SESSION;

    $susername = db::sanitize($username);
    $query = mysqli_query($con, "SELECT id, password FROM people WHERE ".($username === false ? "id = ".(int)$_SESSION["id"] : "username = '".$susername."'"));

    if (!mysqli_num_rows($query)) {
      return false;
    }

    $row = mysqli_fetch_assoc($query);

    if (!password_verify($password, $row["password"])) {
      return false;
    }

    return $row["id"];
  }

  public static function signIn($username, $password) {
    global $_SESSION;

    if (self::isSignInThrottled($username)) return self::SIGNIN_STATE_THROTTLED;

    $id = self::isUserPassword($username, $password);

    if ($id !== false) {
      if (secondFactor::isEnabled($id)) {
        $_SESSION["firstfactorid"] = $id;
        return self::SIGNIN_STATE_NEEDS_SECOND_FACTOR;
      } else {
        $_SESSION["id"] = $id;
        return self::SIGNIN_STATE_SIGNED_IN;
      }
    }

    return self::SIGNIN_STATE_SIGNED_OUT;
  }

  public static function redirectAfterSignIn() {
    global $conf;

    if (self::isAllowed(self::ADMIN)) {
      self::changeActiveView(visual::VIEW_ADMIN);
      self::go("home.php");
    } else {
      self::changeActiveView(visual::VIEW_WORKER);
      self::go(($conf["enableWorkerUI"] ? "workerhome.php" : "index.php?msg=unsupported"));
    }
  }

  public static function logout() {
    global $_SESSION;

    session_destroy();
  }

  public static function htmlsafe($string) {
    if ($string === null) return '';
    return htmlspecialchars((string)$string);
  }

  private static function failedCheckParams($parameter, $method, $check) {
    global $conf;

    if ($conf["superdebug"]) {
      echo "Failed check ".(int)$check." using parameter '".self::htmlsafe($parameter)."' with method ".self::htmlsafe($method).".<br>";
    }
  }

  public static function checkParam($param, $check) {
    if ($check == self::PARAM_NEMPTY && empty($param)) {
      return false;
    }

    if ($check == self::PARAM_ISEMAIL && filter_var($param, FILTER_VALIDATE_EMAIL) === false) {
      return false;
    }

    if ($check == self::PARAM_ISDATE && preg_match("/^[0-9]+-[0-9]+-[0-9]+$/", $param) !== 1) {
      return false;
    }

    if ($check == self::PARAM_ISINT && filter_var($param, FILTER_VALIDATE_INT) === false) {
      return false;
    }

    if ($check == self::PARAM_ISTIME) {
      if (preg_match("/^[0-9]+:[0-9]+$/", $param) !== 1) return false;

      $time = explode(":", $param);
      if ((int)$time[0] >= 24 || (int)$time[1] >= 60) return false;
    }

    if ($check == self::PARAM_ISARRAY) {
      // Check whether the parameter is an array
      if (is_array($param) === false) return false;

      // Check that it is not a multidimensional array (we don't want that for any parameter!)
      foreach ($param as &$el) {
        if (is_array($el)) return false;
      }
    }

    if ($check == self::PARAM_ISEMAILOREMPTY && $param !== "" && filter_var($param, FILTER_VALIDATE_EMAIL) === false) {
      return false;
    }

    return true;
  }

  public static function checkParams($method, $parameters, $forceDisableDebug = false) {
    global $_POST, $_GET;

    if (!in_array($method, ["GET", "POST"])) {
      return false;
    }

    foreach ($parameters as $p) {
      if (!$p[1]) {
        continue;
      }

      if (($method == "POST" && !isset($_POST[$p[0]])) || ($method == "GET" && !isset($_GET[$p[0]]))) {
        if (!$forceDisableDebug) self::failedCheckParams($p[0], $method, self::PARAM_ISSET);
        return false;
      }

      $value = ($method == "POST" ? $_POST[$p[0]] : $_GET[$p[0]]);

      foreach (self::$automatedChecks as $check) {
        if (($p[1] & $check) && !self::checkParam($value, $check)) {
          if (!$forceDisableDebug) self::failedCheckParams($p[0], $method, $check);
          return false;
        }
      }
    }

    return true;
  }

  public static function existsType($type) {
    $types = array_keys(self::$types);

    return in_array($type, $types);
  }

  public static function getActiveView() {
    global $_SESSION;

    if (!self::isAllowed(self::ADMIN)) {
      return visual::VIEW_WORKER;
    }

    return (!isset($_SESSION["activeView"]) ? visual::VIEW_ADMIN : $_SESSION["activeView"]);
  }

  public static function isAdminView() {
    return (self::getActiveView() === visual::VIEW_ADMIN);
  }

  public static function changeActiveView($view) {
    $_SESSION["activeView"] = $view;
  }

  public static function passwordIsGoodEnough($password) {
    return (strlen($password) >= 8 && preg_match('/[A-Z]/', $password));
  }

  public static function cleanSignInAttempts($retentionDays = "DEFAULT") {
    global $con, $conf;

    if ($retentionDays === "DEFAULT") $retentionDays = $conf["signinThrottling"]["retentionDays"];

    $sretentionDays = (int)$retentionDays;
    if ($retentionDays < 0) return false;

    return mysqli_query($con, "DELETE FROM signinattempts WHERE signinattempttime < (NOW() - INTERVAL $sretentionDays DAY)");
  }
}
