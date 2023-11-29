<?php
class help {
  const PLACE_INCIDENT_FORM = 0;
  const PLACE_VALIDATION_PAGE = 1;
  const PLACE_REGISTRY_PAGE = 2;
  const PLACE_EXPORT_REGISTRY_PAGE = 3;

  public static $places = [0, 1, 2, 3];
  public static $placesName = [
    0 => "Formulario de incidencias",
    1 => "Página de validación",
    2 => "Página de listado de registros",
    3 => "Página de exportar registro"
  ];

  public static function exists($place) {
    global $con;

    $splace = (int)$place;

    $query = mysqli_query($con, "SELECT 1 FROM help WHERE place = $splace");

    return (mysqli_num_rows($query) > 0);
  }

  public static function set($place, $url) {
    global $con;

    if (!in_array($place, self::$places)) return -1;
    if ($url !== "" && !filter_var($url, FILTER_VALIDATE_URL)) return 1;

    $splace = (int)$place;
    $surl = db::sanitize($url);

    if (self::exists($place)) return (mysqli_query($con, "UPDATE help SET url = '$surl' WHERE place = $splace LIMIT 1") ? 0 : -1);
    else return (mysqli_query($con, "INSERT INTO help (place, url) VALUES ('$splace', '$surl')") ? 0 : -1);
  }

  public static function get($place) {
    global $con;

    if (!in_array($place, self::$places)) return false;
    $splace = (int)$place;

    $query = mysqli_query($con, "SELECT url FROM help WHERE place = $splace");

    if (mysqli_num_rows($query) > 0) {
      $url = mysqli_fetch_assoc($query)["url"];
      return ($url === "" ? false : $url);
    } else return false;
  }
}
