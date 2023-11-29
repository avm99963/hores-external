<?php
require_once("core.php");

security::logout();
security::go("index.php?msg=logout");
