<?php
require_once("core.php");
security::checkType(security::HYPERADMIN);

if (!security::checkParams("POST", [
  ["format", security::PARAM_ISINT]
])) security::notFound();

switch ($_POST["format"]) {
  default:
  header("Content-Type: application/sql");
  header("Content-Disposition: filename=\"registrohorario_backup_".(int)date("Ymd").".sql\"");
  header("Cache-control: private");
  passthru("mysqldump ".escapeshellarg($conf["db"]["database"])." --host=".escapeshellarg($conf["db"]["host"])." --user=".escapeshellarg($conf["db"]["user"])." --password=".escapeshellarg($conf["db"]["password"]));
}
