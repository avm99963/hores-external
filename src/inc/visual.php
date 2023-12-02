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

class visual {
  const VIEW_ADMIN = 0;
  const VIEW_WORKER = 1;

  // Old:
  /*const YES = "✓";
  const NO = "✗";*/

  // New:
  const YES = "✓";
  const NO = "X";

  public static function snackbar($msg, $timeout = 10000, $printHTML = true) {
    if ($printHTML) echo '<div class="mdl-snackbar mdl-js-snackbar">
      <div class="mdl-snackbar__text"></div>
      <button type="button" class="mdl-snackbar__action"></button>
    </div>';
    echo '<script>
    window.addEventListener("load", function() {
      var notification = document.querySelector(".mdl-js-snackbar");
      notification.MaterialSnackbar.showSnackbar(
        {
          message: "'.security::htmlsafe($msg).'",
          timeout: '.(int)$timeout.'
        }
      );
    });
    </script>';
  }

  public static function smartSnackbar($msgs, $timeout = 10000, $printHTML = true) {
    global $_GET;

    if (!isset($_GET["msg"])) return;

    foreach ($msgs as $msg) {
      if ($_GET["msg"] == $msg[0]) {
        self::snackbar($msg[1], $timeout, $printHTML);
        return;
      }
    }
  }

  public static function debugJson($array) {
    return security::htmlsafe(json_encode($array, JSON_PRETTY_PRINT));
  }

  public static function includeHead() {
    include("includes/head.php");
  }

  public static function includeNav() {
    global $conf, $mdHeaderRowMore, $mdHeaderMore, $mdHeaderRowBefore;

    $activeView = security::getActiveView();
    switch ($activeView) {
      case self::VIEW_ADMIN:
      include("includes/adminnav.php");
      break;

      case self::VIEW_WORKER:
      include("includes/workernav.php");
      break;

      default:
      exit();
    }
  }

  public static function backBtn($url) {
    return '<a class="backbtn mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" href="'.$url.'"><i id="auto_backbtn" class="material-icons">arrow_back</i></a><div class="mdl-tooltip" for="auto_backbtn">Atrás</div><div style="width: 16px;"></div>';
  }

  public static function printDebug($function, $return, $always=false, $notjson=false) {
    global $conf;

    if ($always || $conf["debug"])
      echo '<details class="debug margintop">
        <summary>Debug:</summary>
        <p><b>'.security::htmlsafe($function).'</b></p>
        <div class="overflow-wrapper"><pre>'.($notjson ? security::htmlsafe(print_r($return, true)) : self::debugJson($return)).'</pre></div>
      </details>';
  }

  public static function renderPagination($rows, $page, $limit = 10, $showLimitLink = false, $alreadyHasParameters = false, $limitChange = false, $highlightedPage = false) {
    global $_GET;

    $numPages = ($limit == 0 ? 1 : ceil($rows/$limit));
    if ($numPages > 1) {
      $currentPage = ((isset($_GET["page"]) && $_GET["page"] <= $numPages && $_GET["page"] >= 1) ? $_GET["page"] : 1);

      echo '<div class="pagination">';
      for ($i = 1; $i <= $numPages; $i++) {
        echo ($i != $currentPage ? '<a class="page'.($i == $highlightedPage ? " mdl-color-text--green" : "").'" href="'.security::htmlsafe($page).($alreadyHasParameters ? "&" : "?").'page='.(int)$i.($showLimitLink ? '&limit='.(int)$limit : '').'">'.(int)$i.'</a> ' : '<b class="page">'.(int)$i.'</b> ');
      }
      echo '</div>';
    }

    if ($limitChange !== false) {
      ?>
      <div class="limit-change-container">Ver <select id="limit-change">
        <?php
        if (isset($limitChange["options"])) {
          if (!in_array($limit, $limitChange["options"])) {
            echo "<option value=\"".(int)$limit."\" selected>".(int)$limit."</option>";
          }
          foreach ($limitChange["options"] as $option) {
            echo "<option value=\"".(int)$option."\"".($option == $limit ? " selected" : "").">".(int)$option."</option>";
          }
        }
        ?>
      </select> <?=security::htmlsafe($limitChange["elementName"])?> por página.</div>
      <?php
    }
  }

  public static function padNum($num, $length) {
    return str_pad($num, $length, "0", STR_PAD_LEFT);
  }

  public static function isMDColor() {
    global $conf;

    return (($conf["backgroundColor"][0] ?? "") != "#");
  }

  public static function printBodyTag() {
    global $conf;

    $conf["backgroundColorIsDark"];
    echo "<body ".(!visual::isMDColor() ? "style=\"background-color: ".security::htmlsafe($conf["backgroundColor"]).";\"" : "")."class=\"".(visual::isMDColor() ? "mdl-color--".security::htmlsafe($conf["backgroundColor"]) : "").($conf["backgroundColorIsDark"] ? " dark-background" : "")."\">";
  }

  // WARNING: We will not sanitize $msg, so sanitize it before calling this function!
  public static function addTooltip($id, $msg) {
    global $_tooltips;

    if (!isset($_tooltips)) $_tooltips = "";
    $_tooltips .= '<div class="mdl-tooltip" for="'.security::htmlsafe($id).'">'.$msg.'</div>';
  }

  public static function renderTooltips() {
    global $_tooltips;

    echo ($_tooltips ?? "");
  }

  private static function addMsgToUrl($url, $msg = false) {
    if ($msg === false) return $url;
    return $url.(preg_match("/\?/", $url) == 1 ? "&" : "?")."msg=".urlencode($msg);
  }

  public static function getContinueUrl($defaultUrl, $msg = false, $method = "GET") {
    global $_GET, $_POST;

    $url = "";

    switch ($method) {
      case "GET":
      if (!isset($_GET["continue"])) return self::addMsgToUrl($defaultUrl, $msg);
      $url = (string)$_GET["continue"];
      break;

      case "POST":
      if (!isset($_POST["continue"])) return self::addMsgToUrl($defaultUrl, $msg);
      $url = (string)$_POST["continue"];
      break;

      default:
      return self::addMsgToUrl($defaultUrl, $msg);
    }

    if (!preg_match("/^[^\/\\\\]*$/", $url)) return self::addMsgToUrl($defaultUrl, $msg);

    if ($msg !== false) $url = self::addMsgToUrl($url, $msg);

    return $url;
  }

  public static function addContinueInput($url = false) {
    global $_GET, $_POST;

    if ($url === false) {
      if (isset($_GET["continue"])) $url = $_GET["continue"];
      elseif (isset($_POST["continue"])) $url = $_POST["continue"];
      else return;
    }

    echo '<input type="hidden" name="continue" value="'.security::htmlsafe($url).'">';
  }
}
