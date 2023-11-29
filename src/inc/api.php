<?php
class api {
  public static function inputJson() {
    $string = trim(file_get_contents("php://input"));

    if (empty($string)) return false;

    $json = json_decode($string, true);

    if (json_last_error() !== JSON_ERROR_NONE) return false;

    return $json;
  }

  public static function error($message = null) {
    if ($message !== null) self::write([
      'error' => true,
      'message' => $message,
    ]);
    http_response_code(400);
    exit();
  }

  public static function write($array) {
    header('Content-Type: application/json');
    echo json_encode($array);
  }
}
