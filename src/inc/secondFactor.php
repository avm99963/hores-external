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

require_once(__DIR__."/../lib/GoogleAuthenticator/GoogleAuthenticator.php");
require_once(__DIR__."/../lib/WebAuthn/WebAuthn.php");

class secondFactor {
  public static function isAvailable() {
    global $conf;
    return (($conf["secondFactor"]["enabled"] ?? false) === true);
  }

  public static function checkAvailability() {
    global $conf;

    if (!self::isAvailable()) {
      security::notFound();
    }
  }

  public static function isEnabled($person = "ME") {
    if (!self::isAvailable()) return false;
    return (people::userData("secondfactor", $person) == 1);
  }

  public static function generateSecret() {
    $authenticator = new GoogleAuthenticator();
    return $authenticator->generateSecret();
  }

  public static function isValidSecret($secret) {
    return (strlen($secret) === 32);
  }

  public static function checkCode($secret, $code) {
    $authenticator = new GoogleAuthenticator();
    return $authenticator->checkCode($secret, $code);
  }

  public static function checkPersonCode($person, $code) {
    global $con;

    $sperson = (int)$person;
    $query = mysqli_query($con, "SELECT secret FROM totp WHERE person = $sperson LIMIT 1");
    if ($query === false || !mysqli_num_rows($query)) return false;

    $row = mysqli_fetch_assoc($query);

    return self::checkCode($row["secret"], $code);
  }

  public static function disable($person = "ME", $personDisable = true) {
    global $con;

    if ($person == "ME") $person = people::userData("id");
    $sperson = (int)$person;

    return (mysqli_query($con, "DELETE FROM totp WHERE person = $sperson") && (!$personDisable || mysqli_query($con, "UPDATE people SET secondfactor = 0 WHERE id = $sperson LIMIT 1")) && mysqli_query($con, "DELETE FROM securitykeys WHERE person = $sperson"));
  }

  public static function enable($secret, $person = "ME") {
    global $con;

    if (!self::isValidSecret($secret)) return false;

    if ($person == "ME") $person = people::userData("id");

    self::disable($person, false);

    $sperson = (int)$person;
    $ssecret = db::sanitize($secret);
    return (mysqli_query($con, "INSERT INTO totp (person, secret) VALUES ($sperson, '$ssecret')") && mysqli_query($con, "UPDATE people SET secondfactor = 1 WHERE id = $sperson LIMIT 1"));
  }

  private static function completeLogin($success) {
    if ($success === true) {
      $_SESSION["id"] = $_SESSION["firstfactorid"];
      unset($_SESSION["firstfactorid"]);
      return true;
    }

    if ($success === false) {
      unset($_SESSION["firstfactorid"]);
      return true;
    }

    return false;
  }

  public static function completeCodeChallenge($code) {
    global $_SESSION;

    $success = self::checkPersonCode($_SESSION["firstfactorid"], $code);
    self::completeLogin($success);
    return $success;
  }

  private static function newWebAuthn() {
    global $conf;

    if (!isset($conf["secondFactor"]) || !isset($conf["secondFactor"]["origin"])) {
      throw new Exception('secondFactor is not enabled (or the origin is not set) in config.php.');
    }

    return new \lbuchs\WebAuthn\WebAuthn($conf["appName"], $conf["secondFactor"]["origin"], ["none"]);
  }

  public static function createRegistrationChallenge() {
    global $_SESSION;

    $WebAuthn = self::newWebAuthn();

    $credentialIds = self::getCredentialIds();
    $createArgs = $WebAuthn->getCreateArgs(people::userData("id"), people::userData("username"), people::userData("name"), 20, false, false, ($credentialIds === false ? [] : $credentialIds));
    $_SESSION['webauthnchallenge'] = $WebAuthn->getChallenge();

    return $createArgs;
  }

  public static function addSecurityKeyToDB($credentialId, $credentialPublicKey, $name) {
    global $con;

    $person = people::userData("id");
    if ($person === false) return false;
    $sperson = (int)$person;

    $sname = db::sanitize($name);
    $scredentialId = db::sanitize($credentialId);
    $scredentialPublicKey = db::sanitize($credentialPublicKey);
    $stime = (int)time();

    return mysqli_query($con, "INSERT INTO securitykeys (person, name, credentialid, credentialpublickey, added) VALUES ($sperson, '$sname', '$scredentialId', '$scredentialPublicKey', $stime)");
  }

  public static function completeRegistrationChallenge($clientDataJSON, $attestationObject, $name) {
    global $_SESSION;

    $clientDataJSON = base64_decode($clientDataJSON);
    $attestationObject = base64_decode($attestationObject);

    if (!isset($_SESSION["webauthnchallenge"])) {
      throw new Exception('The user didn\'t start the webauthn challenge.');
    }
    $challenge = $_SESSION["webauthnchallenge"];
    unset($_SESSION["webauthnchallenge"]);

    $WebAuthn = self::newWebAuthn();
    $data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge);

    if (!self::addSecurityKeyToDB($data->credentialId, $data->credentialPublicKey, $name)) {
      throw new Exception('Failed adding security key to DB.');
    }

    return ["status" => "ok"];
  }

  public static function getSecurityKeys($person = "ME") {
    global $con;

    if ($person == "ME") $person = people::userData("id");
    if ($person === false) return false;
    $sperson = (int)$person;

    $query = mysqli_query($con, "SELECT * FROM securitykeys WHERE person = $sperson");
    if ($query === false) return false;

    $securityKeys = [];
    while ($row = mysqli_fetch_assoc($query)) {
      $securityKeys[] = $row;
    }

    return $securityKeys;
  }

  public static function getCredentialIds($person = "ME") {
    $securityKeys = self::getSecurityKeys($person);
    if ($securityKeys === false) return false;

    $credentials = [];
    foreach ($securityKeys as $s) $credentials[] = $s["credentialid"];

    return $credentials;
  }

  public static function hasSecurityKeys($person) {
    global $con;

    $sperson = (int)$person;

    $query = mysqli_query($con, "SELECT 1 FROM securitykeys WHERE person = $sperson LIMIT 1");
    if ($query === false) return false;

    return (mysqli_num_rows($query) > 0);
  }

  public static function createValidationChallenge() {
    global $_SESSION;

    $WebAuthn = self::newWebAuthn();

    if (!isset($_SESSION["firstfactorid"])) {
      throw new Exception('User didn\'t log in with the first factor.');
    }
    $credentialIds = self::getCredentialIds($_SESSION["firstfactorid"]);
    if ($credentialIds === false || empty($credentialIds)) {
      throw new Exception('The user credentials could not be obtained.');
    }

    $getArgs = $WebAuthn->getGetArgs($credentialIds);
    $_SESSION['webauthnvalidationchallenge'] = $WebAuthn->getChallenge();

    return $getArgs;
  }

  public static function getSecurityKey($credentialId, $person) {
    $securityKeys = self::getSecurityKeys($person);

    foreach ($securityKeys as $s) {
      if ($s["credentialid"] == $credentialId) {
        return $s;
      }
    }

    return null;
  }

  public static function getSecurityKeyById($id) {
    global $con;

    $query = mysqli_query($con, "SELECT * FROM securitykeys WHERE id = $id");
    if ($query === false || !mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function removeSecurityKey($id) {
    global $con;

    $sid = (int)$id;
    $sperson = (int)people::userData("id");
    if ($sperson === false) return false;

    return mysqli_query($con, "DELETE FROM securitykeys WHERE id = $sid and PERSON = $sperson LIMIT 1");
  }

  private static function recordSecurityKeyUsageToDB($id) {
    global $con;

    $sid = (int)$id;
    $stime = (int)time();

    return mysqli_query($con, "UPDATE securitykeys SET lastused = $stime WHERE id = $sid LIMIT 1");
  }

  public static function completeValidationChallenge($id, $clientDataJSON, $authenticatorData, $signature) {
    global $_SESSION;

    $id = base64_decode($id);
    $clientDataJSON = base64_decode($clientDataJSON);
    $authenticatorData = base64_decode($authenticatorData);
    $signature = base64_decode($signature);

    if (!isset($_SESSION["webauthnvalidationchallenge"])) {
      throw new Exception('The user didn\'t start the webauthn challenge.');
    }
    $challenge = $_SESSION["webauthnvalidationchallenge"];
    unset($_SESSION["webauthnvalidationchallenge"]);

    $securityKey = self::getSecurityKey($id, $_SESSION["firstfactorid"]);
    $credentialPublicKey = $securityKey["credentialpublickey"] ?? null;
    if ($credentialPublicKey === null) {
      self::completeLogin(false);
      throw new Exception('The security key could not be found.');
    }

    try {
      $WebAuthn = self::newWebAuthn();

      $WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge);
    } catch (Throwable $ex) {
      self::completeLogin(false);
      throw $ex;
    }

    self::recordSecurityKeyUsageToDB($securityKey["id"]);
    self::completeLogin(true);

    return ["status" => "ok"];
  }
}
