<?php
class csv {
  public static $fields = ["dni", "name", "category", "email", "companies"];

  public static function csv2array($filename) {
    $file = fopen($filename, "r");

    $return = [];

    $i = 0;
    while (($line = fgetcsv($file, null, ";")) !== false) {
      if ($i == 0) {
        if (count($line) < count(self::$fields)) return false;

        for ($j = 0; $j < count(self::$fields); $j++) {
          if ($line[$j] !== self::$fields[$j]) return false;
        }
      } else {
        $return[$i] = [];
        foreach (self::$fields as $j => $field) {
          $return[$i][$field] = trim($line[$j]);
        }
      }
      $i++;
    }

    fclose($file);

    return $return;
  }
}
