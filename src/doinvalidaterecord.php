<?php
require_once("core.php");
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();

if (!isset($_POST["id"])) {
  security::notFound();
}

$id = (int)$_POST["id"];

$record = registry::get($id);
if ($record === false || $record["invalidated"] != 0) security::notFound();

$isAdmin = security::isAllowed(security::ADMIN);
if (!$isAdmin) registry::checkRecordIsFromPerson($record["id"]);

security::go((security::isAdminView() ? "registry.php?msg=" : "userregistry.php?id=".$_SESSION["id"]).(registry::invalidate($id) ? "invalidated" : "unexpected"));
