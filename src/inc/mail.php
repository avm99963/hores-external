<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__.'/../lib/PHPMailer/Exception.php';
require __DIR__.'/../lib/PHPMailer/PHPMailer.php';
require __DIR__.'/../lib/PHPMailer/SMTP.php';

class mail {
  public static function send($to, $attachments, $subject, $body, $isHTML = true, $plainBody = "") {
    global $conf;

    if (!$conf["mail"]["enabled"]) return false;

    $mail = new PHPMailer();

    try {
      if ($conf["debug"]) {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      }
      $mail->CharSet = "UTF-8";
      $mail->isSMTP();
      $mail->Host = $conf["mail"]["host"];
      $mail->SMTPAuth = $conf["mail"]["smtpauth"];
      $mail->Username = $conf["mail"]["username"];
      $mail->Password = $conf["mail"]["password"];
      $mail->SMTPSecure = 'tls';
      $mail->Port = $conf["mail"]["port"];
      $mail->Encoding = 'base64';
      $mail->Timeout = 30;

      $mail->setFrom($conf["mail"]["remitent"], $conf["mail"]["remitentName"]);

      foreach ($to as $address) {
        if (isset($address["name"])) $mail->addAddress($address["email"], $address["name"]);
        else $mail->addAddress($address["email"]);
      }

      foreach ($attachments as $attachment) {
        if (isset($attachment["name"])) $mail->addAttachment($attachment["path"], $attachment["name"]);
        else $mail->addAttachment($attachment["email"]);
      }

      if ($isHTML) {
        $mail->isHTML(true);
        if (!empty($plainBody)) $mail->AltBody = $plainBody;
      }

      $mail->Subject = (!empty($conf["mail"]["subjectPrefix"]) ? $conf["mail"]["subjectPrefix"]." " : "").$subject;
      $mail->Body = $body;

      if (!$mail->send()) return false;
    } catch (Exception $e) {
      if ($conf["debug"]) echo $e."\n";
      return false;
    }

    return true;
  }

  public static function bodyTemplate($msg) {
    return "<div style=\"font-family: 'Helvetica', 'Arial', sans-serif;\">".$msg."</div>";
  }
}
