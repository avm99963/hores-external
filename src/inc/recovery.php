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

class recovery {
  const TOKEN_BYTES = 32;
  const EXPIRATION_TIME = 60*60*24;
  const WELCOME_EXPIRATION_TIME = 60*60*24*90;

  const EMAIL_TYPE_RECOVERY = 0;
  const EMAIL_TYPE_WELCOME = 1;

  const EMAIL_NOT_SET = 0;

  public static function getUser($email, $dni) {
    global $con;

    $semail = db::sanitize($email);
    $sdni = db::sanitize($dni);

    $query = mysqli_query($con, "SELECT id FROM people WHERE email = '$semail' AND dni = '$sdni' LIMIT 1");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query)["id"];
  }

  private static function tokenIsUnique($token) {
    global $con;

    $stoken = db::sanitize($token);

    $query = mysqli_query($con, "SELECT 1 FROM recovery WHERE token = '$stoken' LIMIT 1");

    return !mysqli_num_rows($query);
  }

  private static function generateRandomToken() {
    do {
      $token = bin2hex(random_bytes(self::TOKEN_BYTES));
    } while (!self::tokenIsUnique($token));

    return $token;
  }

  private static function invalidateAllRecoveries($id) {
    global $con;

    $sid = (int)$id;

    return mysqli_query($con, "UPDATE recovery SET used = 1 WHERE user = $sid");
  }

  public static function sendRecoveryMail($user, $token, $expires, $emailType = self::EMAIL_TYPE_RECOVERY) {
    global $conf;

    $to = [
      ["email" => $user["email"]]
    ];

    $url = security::htmlsafe($conf["fullPath"]."recovery.php?token=".$token);

    switch ($emailType) {
      case self::EMAIL_TYPE_WELCOME:
      $subject = "Datos de acceso de ".security::htmlsafe($user["name"]);
      $body = mail::bodyTemplate("<p>Hola ".security::htmlsafe($user["name"]).",</p>
      <p>Para poder acceder al aplicativo de control horario, adjuntamos un enlace donde podrás configurar tu contraseña:</p>
      <ul>
        <li><a href=\"$url\">$url</a></li>
      </ul>
      <p>Una vez establezcas tu contraseña, podrás iniciar sesión con la contraseña que has rellenado y tu usuario: <b><code>".security::htmlsafe($user["username"])."</code></b></p>
      <p>Reciba un cordial saludo.</p>");
      break;

      default:
      $subject = "Recuperar contraseña de ".security::htmlsafe($user["name"]);
      $body = mail::bodyTemplate("<p>Hola ".security::htmlsafe($user["name"]).",</p>
      <p>Alguien (seguramente tú) ha rellenado el formulario de recuperación de contraseñas para el aplicativo de registro horario.</p>
      <p>Si no has sido tú, puede que alguien esté intentando entrar en tu zona de trabajador del aplicativo, y te agredeceríamos que lo comunicaras en la mayor brevedad posible a recursos humanos.</p>
      <p>Si has sido tú, puedes restablecer tu contraseña haciendo clic en el siguiente enlace:</p>
      <ul>
        <li><a href=\"$url\">$url</a></li>
      </ul>
      <p>Además, aprovechamos para recordarte que tu usuario en el web es <b><code>".security::htmlsafe($user["username"])."</code></b></p>
      <p>Reciba un cordial saludo.</p>");
    }

    return mail::send($to, [], $subject, $body);
  }

  public static function recover($id, $emailType = self::EMAIL_TYPE_RECOVERY) {
    global $con;

    $user = people::get($id);
    if ($user === false) return false;
    if (!isset($user["email"]) || empty($user["email"])) return self::EMAIL_NOT_SET;

    $token = self::generateRandomToken();
    $stoken = db::sanitize($token);
    $sid = (int)$id;
    $stime = (int)time();
    $sexpires = (int)($stime + ($emailType === self::EMAIL_TYPE_WELCOME ? self::WELCOME_EXPIRATION_TIME : self::EXPIRATION_TIME));

    if (!self::invalidateAllRecoveries($id)) return false;

    if (!mysqli_query($con, "INSERT INTO recovery (user, token, timecreated, expires) VALUES ($sid, '$stoken', $stime, $sexpires)")) return false;

    return self::sendRecoveryMail($user, $token, $sexpires, $emailType);
  }

  public static function getRecovery($token) {
    global $con;

    $stoken = db::sanitize($token);

    $query = mysqli_query($con, "SELECT * FROM recovery WHERE token = '$stoken' LIMIT 1");

    if (!mysqli_num_rows($query)) return false;

    return mysqli_fetch_assoc($query);
  }

  public static function getUnusedRecovery($token) {
    $recovery = self::getRecovery($token);
    if ($recovery === false || $recovery["used"] != 0 || $recovery["expires"] < time()) return false;

    return $recovery;
  }

  public static function finishRecovery($token, $password) {
    global $con;

    $stoken = db::sanitize($token);
    $spassword = db::sanitize(password_hash($password, PASSWORD_DEFAULT));

    $recovery = recovery::getUnusedRecovery($token);
    if ($recovery === false) return false;

    if (!self::invalidateAllRecoveries($recovery["user"])) return false;

    return people::updatePassword($recovery["user"], $spassword);
  }
}
