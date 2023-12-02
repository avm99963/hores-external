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

require_once("core.php");
security::checkType(security::ADMIN);

if (!security::checkParams("POST", [
  ["incidents", security::PARAM_ISARRAY]
])) {
  security::go("incidents.php?msg=empty");
}

$allOk = true;
foreach ($_POST["incidents"] as $id) {
  $incident = incidents::get($id, true);
  if ($incident === false) security::go($returnURL."msg=unexpected");

  $istatus = incidents::getStatus($incident);

  if (in_array($istatus, incidents::$canRemoveStates)) {
    if (!incidents::remove($id)) $allOk = false;
  } elseif (in_array($istatus, incidents::$canInvalidateStates)) {
    if (!incidents::invalidate($id)) $allOk = false;
  } else $allOk = false;
}

security::go("incidents.php?msg=deleteincidentsbulk".($allOk ? "success" : "partialsuccess"));
