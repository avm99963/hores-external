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

if (php_sapi_name() != "cli") {
  exit();
}

// Classes autoload
spl_autoload_register(function($className) {
  if ($className == "lbuchs\WebAuthn\Binary\ByteBuffer") include_once(__DIR__."/lib/WebAuthn/Binary/ByteBuffer.php");
  else include_once(__DIR__."/inc/".$className.".php");
});

require_once("config.php");
$con = @mysqli_connect($conf["db"]["server"], $conf["db"]["user"], $conf["db"]["password"], $conf["db"]["database"]) or die("There was an error connecting to the database.");
mysqli_set_charset($con, "utf8mb4");

echo "Benvingut a l'instal·lador de l'aplicació 'Hores'.\n\n";
echo "Entra els detalls del primer usuari administrador de l'aplicació:\n";
echo "Nom d'usuari: ";
$username = mysqli_real_escape_string($con, trim(fgets(STDIN)));

echo "Contrasenya: ";
system('stty -echo');
$pw = mysqli_real_escape_string($con, password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT));
system('stty echo');
echo "\n";

echo "Nom complet: ";
$name = mysqli_real_escape_string($con, trim(fgets(STDIN)));

echo "DNI: ";
$dni = mysqli_real_escape_string($con, trim(fgets(STDIN)));

echo "Correu electrònic: ";
$email = mysqli_real_escape_string($con, trim(fgets(STDIN)));

echo "\nGenerant taules de la base de dades:\n";

$sql = [];

$sql["people"] = "CREATE TABLE people (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL UNIQUE,
  type INT,
  name VARCHAR(255) NOT NULL,
  dni VARCHAR(10) NOT NULL,
  email VARCHAR(255) NOT NULL,
  category INT DEFAULT -1,
  INDEX(category),
  secondfactor INT DEFAULT 0
)";

$sql["companies"] = "CREATE TABLE companies (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  name VARCHAR(100) NOT NULL UNIQUE,
  cif VARCHAR(100) NOT NULL
)";

$sql["workers"] = "CREATE TABLE workers (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  person INT NOT NULL,
  INDEX(person),
  company INT NOT NULL,
  INDEX(company)
)";

$sql["workhistory"] = "CREATE TABLE workhistory (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT NOT NULL,
  INDEX(worker),
  day INT NOT NULL,
  status INT NOT NULL
)";

$sql["categories"] = "CREATE TABLE categories (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  name VARCHAR(100) NOT NULL UNIQUE,
  parent INT NOT NULL,
  emails TEXT
)";

$sql["calendars"] = "CREATE TABLE calendars (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  category INT NOT NULL,
  begins INT NOT NULL,
  ends INT NOT NULL,
  details TEXT NOT NULL
)";

$sql["scheduletemplates"] = "CREATE TABLE scheduletemplates (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  name VARCHAR(50) NOT NULL UNIQUE,
  begins INT NOT NULL,
  ends INT NOT NULL
)";

$sql["scheduletemplatesdays"] = "CREATE TABLE scheduletemplatesdays (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  template INT NOT NULL,
  INDEX(template),
  day INT NOT NULL,
  typeday INT NOT NULL,
  beginswork INT NOT NULL,
  endswork INT NOT NULL,
  beginsbreakfast INT,
  endsbreakfast INT,
  beginslunch INT,
  endslunch INT
)";

$sql["schedules"] = "CREATE TABLE schedules (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT NOT NULL,
  INDEX(worker),
  begins INT NOT NULL,
  ends INT NOT NULL,
  active INT NOT NULL
)";

$sql["schedulesdays"] = "CREATE TABLE schedulesdays (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  schedule INT NOT NULL,
  INDEX(schedule),
  day INT NOT NULL,
  typeday INT NOT NULL,
  beginswork INT NOT NULL,
  endswork INT NOT NULL,
  beginsbreakfast INT,
  endsbreakfast INT,
  beginslunch INT,
  endslunch INT
)";

$sql["typesincidents"] = "CREATE TABLE typesincidents (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  name VARCHAR(100) NOT NULL UNIQUE,
  present INT NOT NULL,
  paid INT NOT NULL,
  workerfill INT NOT NULL DEFAULT 0,
  notifies INT NOT NULL DEFAULT 0,
  autovalidates INT NOT NULL DEFAULT 0,
  hidden INT NOT NULL DEFAULT 0
)";

$sql["incidents"] = "CREATE TABLE incidents (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT,
  INDEX(worker),
  creator INT,
  updatedby INT DEFAULT -1,
  confirmedby INT DEFAULT -1,
  type INT NOT NULL,
  day INT NOT NULL,
  INDEX(day),
  begins INT NOT NULL,
  ends INT NOT NULL,
  details TEXT,
  workerdetails TEXT DEFAULT NULL,
  attachments TEXT,
  verified INT DEFAULT 0,
  invalidated INT DEFAULT 0,
  INDEX(invalidated),
  workervalidated INT DEFAULT 0,
  INDEX(workervalidated),
  workervalidation TEXT
)";

/*$sql["recurringincidents"] = "CREATE TABLE recurringincidents (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT,
  creator INT,
  type INT NOT NULL,
  firstday INT NOT NULL,
  lastday INT NOT NULL,
  typedays TEXT,
  begins INT NOT NULL,
  ends INT NOT NULL,
  details TEXT
)";*/ // NOTE: No longer needed

$sql["records"] = "CREATE TABLE records (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  worker INT NOT NULL,
  INDEX(worker),
  day INT NOT NULL,
  INDEX(day),
  created INT NOT NULL,
  creator INT NOT NULL,
  beginswork INT NOT NULL,
  endswork INT NOT NULL,
  beginsbreakfast INT NOT NULL,
  endsbreakfast INT NOT NULL,
  beginslunch INT NOT NULL,
  endslunch INT NOT NULL,
  invalidated INT DEFAULT 0,
  INDEX(invalidated),
  invalidatedby INT DEFAULT -1,
  workervalidated INT DEFAULT 0,
  INDEX(workervalidated),
  workervalidation TEXT
)";

$sql["logs"] = "CREATE TABLE logs (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  realtime INT NOT NULL,
  day INT NOT NULL,
  logdetails TEXT,
  executedby INT DEFAULT -1
)";

$sql["recovery"] = "CREATE TABLE recovery (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  user INT NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  timecreated INT NOT NULL,
  expires INT NOT NULL,
  used INT DEFAULT 0
)";

$sql["help"] = "CREATE TABLE help (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  place INT NOT NULL UNIQUE,
  url VARCHAR(256) NOT NULL
)";

$sql["totp"] = "CREATE TABLE totp (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  person INT NOT NULL UNIQUE,
  secret VARCHAR(256) NOT NULL
)";

$sql["securitykeys"] = "CREATE TABLE securitykeys (
  id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(id),
  person INT NOT NULL,
  name VARCHAR(100),
  credentialid VARBINARY(500),
  credentialpublickey TEXT,
  added INT NOT NULL,
  lastused INT
)";

$sql["signinattempts"] = "CREATE TABLE signinattempts (
  username VARCHAR(100) NOT NULL,
  KEY username (username),
  remoteip VARBINARY(16) NOT NULL,
  KEY remoteip (remoteip),
  remoteipblock VARBINARY(16) NOT NULL,
  KEY remoteipblock (remoteipblock),
  signinattempttime DATETIME NOT NULL,
  KEY signinattempttime (signinattempttime)
)";

foreach ($sql as $table => $sentence) {
  if (mysqli_query($con, $sentence)) {
    echo "Taula ".$table." creada satisfactòriament.\n";
  } else {
    die("Hi ha hagut un error creant la taula ".$table.": ".mysqli_error($con)."\n");
  }
}

echo "\n";

if (mysqli_query($con, "INSERT INTO people (username, password, type, name, dni, email) VALUES ('".$username."', '".$pw."', 0, '".$name."', '".$dni."', '".$email."')")) {
  echo "Afegit el primer usuari satisfactòriament.\n\n";
  echo "Instal·lació completada correctament.\n";
} else {
  echo "Hi ha hagut un error afegint el primer usuari.\n";
}
