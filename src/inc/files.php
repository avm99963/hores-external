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

class files {
  const NAME_LENGTH = 16;
  const MAX_SIZE = 6*1024*1024;
  const READABLE_MAX_SIZE = "6 MB";

  public static $acceptedFormats = ["pdf", "png", "jpg", "jpeg", "bmp", "gif"];
  public static $mimeTypes = array(
    "pdf" => "application/pdf",
    "png" => "image/png",
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "bmp" => "image/bmp",
    "gif" => "image/gif"
  );
  public static $readableMimeTypes = array(
    "pdf" => "Documento PDF",
    "png" => "Imagen PNG",
    "jpg" => "Imagen JPG",
    "jpeg" => "Imagen JPG",
    "bmp" => "Imagen BMP",
    "gif" => "Imagen GIF"
  );
  public static $mimeTypesIcons = array(
    "pdf" => "insert_drive_file",
    "png" => "image",
    "jpg" => "image",
    "jpeg" => "image",
    "bmp" => "image",
    "gif" => "image"
  );

  public static function getAcceptAttribute($pretty = false) {
    $formats = array_map(function($el) {
      return ".".$el;
    }, self::$acceptedFormats);

    return implode(($pretty ? ", " : ","), $formats);
  }

  // From https://stackoverflow.com/a/31107425
  private static function randomStr(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
    if ($length < 1) {
      throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
      $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
  }


  private static function getName($ext) {
    global $conf;

    $filename = "";

    do {
      $filename = self::randomStr(self::NAME_LENGTH).".".$ext;
    } while (file_exists($conf["attachmentsFolder"].$filename));

    return $filename;
  }

  public static function getFileExtension($file) {
    $filenameExploded = explode(".", $file);
    return mb_strtolower($filenameExploded[count($filenameExploded) - 1]);
  }

  public static function uploadFile(&$file, &$name) {
    global $conf;

    if (!isset($file["error"]) || is_array($file["error"])) return 1;

    switch ($file["error"]) {
      case UPLOAD_ERR_OK:
      break;

      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
      return 2;
      break;

      default:
      return 1;
      break;
    }

    $ext = self::getFileExtension($file["name"]);

    if ($file['size'] > self::MAX_SIZE) return 2;
    if (!in_array($ext, self::$acceptedFormats)) return 3;

    $name = self::getName($ext);

    return (move_uploaded_file($file["tmp_name"], $conf["attachmentsFolder"].$name) ? 0 : 1);
  }

  public static function removeFile($name) {
    global $conf;

    return unlink($conf["attachmentsFolder"].$name);
  }
}
