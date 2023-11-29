<?php
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
