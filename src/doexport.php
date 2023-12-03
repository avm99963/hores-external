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
security::checkType(security::WORKER);
security::checkWorkerUIEnabled();

$isAdmin = security::isAllowed(security::ADMIN);

$mainURL = (security::isAdminView() ? "export.php" : "export4worker.php");

if (!security::checkParams("GET", [
  ["begins", security::PARAM_ISDATE],
  ["ends", security::PARAM_ISDATE],
  ["workers", security::PARAM_ISARRAY],
  ["format", security::PARAM_ISINT]
])) {
  security::go($mainURL."?msg=empty");
}

$ignoreempty = (isset($_GET["ignoreempty"]) && $_GET["ignoreempty"] == 1);
$labelinvalid = (isset($_GET["labelinvalid"]) && $_GET["labelinvalid"] == 1);
$showvalidated = (isset($_GET["showvalidated"]) && $_GET["showvalidated"] == 1);
$shownotvalidated = (isset($_GET["shownotvalidated"]) && $_GET["shownotvalidated"] == 1);

$begins = new DateTime($_GET["begins"]);
$ends = new DateTime($_GET["ends"]);
$interval = new DateInterval("P1D");

if (!intervals::wellFormed([$begins, $ends])) {
  security::go($mainURL."?msg=inverted");
}

$date = new DateTime(date("Y-m-d")."T00:00:00");
$interval = new DateInterval("P1D");
$date->sub($interval);

if ($ends->diff($date)->invert !== 0) {
  security::go($mainURL."?msg=forecastingthefutureisimpossible");
}

$companies = companies::getAll(false, true);
if ($companies === false) {
  security::go($mainURL."?msg=unexpected");
}

$filenameDate = $begins->format("Ymd")."_".$ends->format("Ymd");

if (!$isAdmin && !in_array($_GET["format"], export::$workerFormats)) {
  echo "Todavía no implementado.\n";
  exit();
}

switch ($_GET["format"]) {
  case export::FORMAT_PDF:
  case export::FORMAT_DETAILEDPDF:
  require_once("lib/fpdf/fpdf.php");
  require_once("lib/fpdf-easytable/exfpdf.php");
  require_once("lib/fpdf-easytable/easyTable.php");

  $actualTime = time();

  class PDF extends EXFPDF {
    function Footer() {
      global $actualTime;

      $this->SetFont('Arial','I',10);
      $this->SetY(-20);
      $this->Cell(0, 10, export::convert("Generado: ".date::getShortDateWithTime($actualTime)), 0, 0, 'L');
      $this->SetY(-20);
      $this->Cell(0, 10, $this->PageNo().'/{nb}', 0, 0, 'R');
    }
  }

  $pdf = new PDF();
  $pdf->SetCompression(true);
  $pdf->SetCreator(export::convert($conf["appName"]));
  $pdf->SetTitle(export::convert("Registro".($showvalidated && !$shownotvalidated ? " (validados)" : "").(!$showvalidated && $shownotvalidated ? " (no validados)" : "")));
  $pdf->SetAutoPageBreak(false, 22);
  $pdf->AliasNbPages();
  $pdf->SetFont('Arial','',12);

  $headerCells = ["Fecha", "Inicio jornada", "Fin jornada", "Desayuno", "Comida", ""];
  $headerCells = array_map(function($value) {
    return export::convert($value);
  }, $headerCells);
  $spacingDayRow = "%{18, 18, 18, 19, 19, 8}";

  foreach ($_GET["workers"] as $workerid) {
    $worker = workers::get($workerid);
    if ($worker === false || (!$isAdmin && $worker["person"] != $_SESSION["id"])) continue;

    $days = export::getDays($worker["id"], $begins->getTimestamp(), $ends->getTimestamp(), $showvalidated, $shownotvalidated);

    if (count($days) || !$ignoreempty) {
      $pdf->AddPage();
      $pdf->SetMargins(17, 17);
      $pdf->SetY(17);

      $header = new easyTable($pdf, 1, 'border-width: 0.01; border: 0; align: L;');
      $header->easyCell(export::convert($worker["name"].(!empty($worker["dni"]) ? " (".$worker["dni"].")" : "")), 'font-style:B;');
      $header->printRow();
      $header->easyCell(export::convert($worker["companyname"].(!empty($companies[$worker["company"]]["cif"]) ? " (CIF: ".$companies[$worker["company"]]["cif"].")" : "")));
      $header->printRow();
      $header->endTable(6);
    }

    if (count($days)) {
      $totalEffectiveTime = 0;
      $totalWorkTime = 0;
      $incidentsTime = [];

      $table = new easyTable($pdf, $spacingDayRow, 'border: "TB"; border-width: 0.15;');
      foreach ($headerCells as $i => $cell) {
        $table->easyCell($cell, 'font-style: B;'.($i == 0 ? "" : " align: C;"));
      }
      $table->printRow(true);
      $table->endTable(0);

      foreach ($days as $timestamp => $day) {
        $issetSchedule = isset($day["schedule"]);
        $showSchedule = $issetSchedule;
        if ($showSchedule && isset($day["incidents"])) {
          foreach ($day["incidents"] as $incident) {
            if ($incident["allday"] == true && $incident["typepresent"] == 0) {
              $showSchedule = false;
              break;
            }
          }
        }

        $workInt = ($issetSchedule ? [$day["schedule"]["beginswork"], $day["schedule"]["endswork"]] : [incidents::STARTOFDAY, incidents::STARTOFDAY]);
        $notWorkIntA = ($issetSchedule ? [incidents::STARTOFDAY, $day["schedule"]["beginswork"]] : [incidents::STARTOFDAY, incidents::ENDOFDAY]);
        $notWorkIntB = ($issetSchedule ? [$day["schedule"]["endswork"], incidents::ENDOFDAY] : [incidents::ENDOFDAY, incidents::ENDOFDAY]);
        $breakfastInt = ($issetSchedule ? [$day["schedule"]["beginsbreakfast"], $day["schedule"]["endsbreakfast"]] : [incidents::STARTOFDAY, incidents::STARTOFDAY]);
        $lunchInt = ($issetSchedule ? [$day["schedule"]["beginslunch"], $day["schedule"]["endslunch"]] : [incidents::STARTOFDAY, incidents::STARTOFDAY]);

        $effectiveTime = intervals::measure($workInt) - (intervals::measure($breakfastInt) + intervals::measure($lunchInt));

        $totalWorkTime += $effectiveTime;

        if (isset($day["incidents"])) {
          foreach ($day["incidents"] as &$incident) {
            $incidentInt = [$incident["begins"], $incident["ends"]];

            $incidentTime = 0;

            if ($incident["typepresent"] == 1) {
              $incidentTime = intervals::measureIntersection($incidentInt, $notWorkIntA) + intervals::measureIntersection($incidentInt, $notWorkIntB) + intervals::measureIntersection($incidentInt, $breakfastInt) + intervals::measureIntersection($incidentInt, $lunchInt);
              $effectiveTime = $effectiveTime + $incidentTime;

              $incidentTime = -$incidentTime;
            } else {
              $incidentTime = intervals::measureIntersection($incidentInt, $workInt) - ($conf["pdfs"]["workersAlwaysHaveBreakfastAndLunch"] ? 0 : (intervals::measureIntersection($incidentInt, $breakfastInt) + intervals::measureIntersection($incidentInt, $lunchInt)));
              $effectiveTime = $effectiveTime - $incidentTime;
            }

            $incidentsTime[$incident["typename"]] = (isset($incidentsTime[$incident["typename"]]) ? $incidentsTime[$incident["typename"]] : 0) + $incidentTime;
          }
        }

        $effectiveTime = max(incidents::STARTOFDAY, min($effectiveTime, incidents::ENDOFDAY));
        $totalEffectiveTime += $effectiveTime;

        $labelAsInvalid = ($shownotvalidated && isset($day["schedule"]) && $day["schedule"]["state"] === registry::STATE_REGISTERED);

        $table = new easyTable($pdf, $spacingDayRow, 'border: "BT";'.(isset($day["incidents"]) ? ' border-width: 0.07; border-color: #555;' : ' border-width: 0.15;').($labelAsInvalid && $labelinvalid ? ' font-color: #F00;' : ''));
        $table->easyCell(export::convert(date("d/m/Y", $timestamp).($labelAsInvalid ? " (nv)" : "")));
        $table->easyCell(export::convert(($showSchedule ? schedules::sec2time($day["schedule"]["beginswork"]) : "-")), 'align: C;');
        $table->easyCell(export::convert(($showSchedule ? schedules::sec2time($day["schedule"]["endswork"]) : "-")), 'align: C;');
        $table->easyCell(export::convert(($showSchedule ? (intervals::measure($breakfastInt) == 0 ? "-" : (!$conf["pdfs"]["showExactTimeForBreakfastAndLunch"] ? export::sec2hours(intervals::measure($breakfastInt)) : schedules::sec2time($day["schedule"]["beginsbreakfast"])." - ".schedules::sec2time($day["schedule"]["endsbreakfast"]))) : "-")), 'align: C;');
        $table->easyCell(export::convert(($showSchedule ? (intervals::measure($lunchInt) == 0 ? "-" : (!$conf["pdfs"]["showExactTimeForBreakfastAndLunch"] ? export::sec2hours(intervals::measure($lunchInt)) : schedules::sec2time($day["schedule"]["beginslunch"])." - ".schedules::sec2time($day["schedule"]["endslunch"]))) : "-")), 'align: C;');
        $table->easyCell(export::convert(export::sec2hours($effectiveTime)), 'font-style: B; align: R;');
        $table->printRow();
        $table->endTable(0);

        if (isset($day["incidents"])) {
          $incidentstable = new easyTable($pdf, "%{7, 15, 20, 20, 38}", 'border: "BT"; border-width: 0.15; font-color: #555;');
          foreach ($day["incidents"] as &$incident) {
            $labelAsInvalid = ($incident["state"] === incidents::STATE_REGISTERED);

            if ($labelAsInvalid && $labelinvalid) $incidentstable->rowStyle('font-color: #F00;');
            $incidentstable->easyCell("");
            $incidentstable->easyCell(export::convert("Incidencia:"), 'font-style: B;');
            $incidentstable->easyCell(export::convert($incident["typename"]), 'align: C; font-size: 11;');
            $incidentstable->easyCell(export::convert("(".(($incident["begins"] == 0 && $incident["ends"] == incidents::ENDOFDAY) ? "todo el día" : schedules::sec2time($incident["begins"])." - ".schedules::sec2time($incident["ends"])).")"), 'align: C;');
            $incidentstable->easyCell(export::convert((!empty($incident["details"]) ? "Obs.: ".$incident["details"] : "").($labelAsInvalid ? "\n(No validada)" : "")), 'font-size: 10;');
            $incidentstable->printRow();
          }
          $incidentstable->endTable(0);
        }
      }

      if ($_GET["format"] == export::FORMAT_DETAILEDPDF) {
        $pdf->Ln();
        $pdf->Ln();
        $table = new easyTable($pdf, "%{77, 23}", 'align: R; width: 100; border: "BT"; border-width: 0.15;');

        $table->easyCell(export::convert("Tiempo de trabajo programado"));
        $table->easyCell(export::convert(export::sec2hours($totalWorkTime)), 'align: R;');
        $table->printRow();

        foreach ($incidentsTime as $incidentName => $incidentTime) {
          $table->easyCell(export::convert($incidentName));
          $table->easyCell(export::convert(($incidentTime <= 0 ? "+" : "\u{2013}")." ".export::sec2hours(abs($incidentTime))), 'align: R;');
          $table->printRow();
        }

        $table->easyCell(export::convert("Tiempo trabajado"), 'font-style: B;');
        $table->easyCell(export::convert(export::sec2hours($totalEffectiveTime)), 'align: R; font-style: B;');
        $table->printRow();

        $table->endTable(0);
      }
    } elseif (!$ignoreempty) {
      $pdf->Cell(0, 0, "No hay datos para este trabajador.", 0, 0, 'C');
    }
  }

  $pdf->Output("I", "registrohorario_".$filenameDate.".pdf");
  break;

  case export::FORMAT_CSV_SCHEDULES:
  case export::FORMAT_CSV_INCIDENTS:
  $isSchedules = ($_GET["format"] == export::FORMAT_CSV_SCHEDULES);
  $field = ($isSchedules ? "schedule" : "incidents");
  header("Content-Type: text/csv");
  header("Content-Disposition: attachment;filename=".($isSchedules ? "schedules_".$filenameDate.".csv" : "incidents_".$filenameDate.".csv"));

  $array = [];

  $array[] = ($isSchedules ? export::$schedulesFields : export::$incidentsFields);

  foreach ($_GET["workers"] as $workerid) {
    $worker = workers::get($workerid);
    if ($worker === false || (!$isAdmin && $worker["person"] != $_SESSION["id"])) continue;

    $days = export::getDays($worker["id"], $begins->getTimestamp(), $ends->getTimestamp(), true, true);

    foreach ($days as &$day) {
      if (isset($day[$field])) {
        if ($isSchedules) {
          $schedule = [];
          foreach (export::$schedulesFields as $i => $key) {
            $types = ["breakfast", "lunch"];
            $typesNotDefined = [];
            foreach ($types as $type) {
              if (intervals::measure([$day[$field]["begins".$type], $day[$field]["ends".$type]]) == 0) {
                $typesNotDefined[] = "begins".$type;
                $typesNotDefined[] = "ends".$type;
              }
            }

            switch ($key) {
              case "worker":
              $schedule[$i] = $worker["name"];
              break;

              case "company":
              $schedule[$i] = $worker["companyname"];
              break;

              case "workerid":
              $schedule[$i] = $worker["id"];
              break;

              case "dni":
              $schedule[$i] = $worker["dni"];
              break;

              case "day":
              $schedule[$i] = date("d/m/Y", $day[$field][$key]);
              break;

              case "state":
              $schedule[$i] = (registry::$stateTooltips[$day[$field][$key]]);
              break;

              case "beginswork":
              case "endswork":
              case "beginsbreakfast":
              case "endsbreakfast":
              case "beginslunch":
              case "endslunch":
              $schedule[$i] = (in_array($key, $typesNotDefined) ? "-" : schedules::sec2time($day[$field][$key]));
              break;

              default:
              $schedule[$i] = $day[$field][$key];
            }
          }
          $array[] = $schedule;
        } else {
          foreach ($day[$field] as &$incident) {
            $convIncident = [];
            foreach (export::$incidentsFields as $i => $key) {
              switch ($key) {
                case "worker":
                $convIncident[$i] = $worker["name"];
                break;

                case "company":
                $convIncident[$i] = $worker["companyname"];
                break;

                case "workerid":
                $convIncident[$i] = $worker["id"];
                break;

                case "dni":
                $convIncident[$i] = $worker["dni"];
                break;

                case "creator":
                case "updatedby":
                case "confirmedby":
                $convIncident[$i] = (($key == "updatedby" && $incident["updatestate"] != 1) ? "-" : people::userData("name", $incident[$key]));
                break;

                case "day":
                $convIncident[$i] = date("d/m/Y", $incident[$key]);
                break;

                case "begins":
                case "ends":
                $convIncident[$i] = ($incident["allday"] == 1 ? "-" : schedules::sec2time($incident[$key]));
                break;

                case "updated":
                case "verified":
                case "typepresent":
                case "typepaid":
                if ($key == "updated") $key = "updatestate";
                $convIncident[$i] = ($incident[$key] == -1 ? "-" : ($incident[$key] == 1 ? visual::YES : visual::NO));
                break;

                case "allday":
                $convIncident[$i] = ($incident[$key] == 1 ? visual::YES : visual::NO);
                break;

                case "state":
                $convIncident[$i] = (incidents::$stateTooltips[$incident[$key]]);
                break;

                case "type":
                $convIncident[$i] = $incident["typename"];
                break;

                default:
                $convIncident[$i] = $incident[$key];
              }
            }
            $array[] = $convIncident;
          }
        }
      }
    }
  }

  if (!count($array)) exit();

  $df = fopen("php://output", 'w');

  foreach ($array as $row) fputcsv($df, $row);

  fclose($df);
  break;

  default:
  header("Content-Type: text/plain;");
  echo "Todavía no implementado.\n";
}
