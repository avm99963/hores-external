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

// Core of the application

const INTERNAL_CLASS_NAMESPACE = "Internal\\";

// Classes autoload
spl_autoload_register(function($className) {
  if ($className == "lbuchs\WebAuthn\Binary\ByteBuffer") {
    include_once(__DIR__."/lib/WebAuthn/Binary/ByteBuffer.php");
    return;
  }


  include_once(__DIR__."/inc/".$className.".php");
});

// Getting configuration
require_once(__DIR__."/config.php");

// Setting timezone and locale accordingly
date_default_timezone_set("Europe/Madrid");
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');

// Database settings
$con = @mysqli_connect($conf["db"]["server"], $conf["db"]["user"], $conf["db"]["password"], $conf["db"]["database"]) or die("There was an error connecting to the database.\n");
mysqli_set_charset($con, "utf8mb4");

// Session settings
session_set_cookie_params([
  "lifetime" => 0,
  "path" => $conf["path"],
  "httponly" => true
]);
session_start();

// Check if app has been installed
if (db::needsSetUp()) {
  security::logout();
  die("Please, run install.php from the command line to install the app before using it.");
}
