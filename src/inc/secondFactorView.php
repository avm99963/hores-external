<?php
class secondFactorView {
  public static function renderSecret($secret) {
    for ($i = 0; $i < strlen($secret); $i++) {
      if ($i != 0 && $i % 4 == 0) echo " ";
      echo $secret[$i];
    }
  }
}
