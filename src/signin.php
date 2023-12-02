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

if (!isset($_POST["username"]) || !isset($_POST["password"]) || empty($_POST["username"]) || empty($_POST["password"])) {
  security::go("index.php?msg=empty");
}

switch (security::signIn($_POST["username"], $_POST["password"])) {
  case security::SIGNIN_STATE_SIGNED_IN:
  security::redirectAfterSignIn();
  break;

  case security::SIGNIN_STATE_NEEDS_SECOND_FACTOR:
  security::go("signinsecondfactor.php");
  break;

  case security::SIGNIN_STATE_THROTTLED:
  security::go("index.php?msg=signinthrottled");
  break;

  default:
  security::go("index.php?msg=wrong");
}
