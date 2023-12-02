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
