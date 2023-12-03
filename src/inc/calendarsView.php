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

class calendarsView {
  public static function renderCalendar($current, $ends, $selectedFunc, $disabled = false, $extra = false) {
    $interval = new DateInterval("P1D");

    echo '<div class="overflow-wrapper">';

    $start = true;
    $day = 0;
    while ($current->diff($ends)->invert === 0) {
      $dow = (int)$current->format("w");
      if ($dow == 0) $dow = 7;
      $dom = (int)$current->format("d");

      if ($dow == 1) echo "</tr>";
      if ($dom == 1) echo "</table>";
      if ($dom == 1 || $start) echo "<div class='month'>".security::htmlsafe(ucfirst(date::getMonthYear($current->getTimestamp())))."</div><table class='calendar'>";
      if ($dow == 1 || $start) echo "<tr>";
      if ($dom == 1 || $start) {
        for ($i = 1; $i < $dow; $i++) {
          echo "<td></td>";
        }
      }

      echo "<td class='day'><span class='date'>".$dom."</span><br><select name='type[$day]'".($disabled ? " disabled" : "").">";

      foreach (calendars::$types as $id => $type) {
        echo "<option value='".(int)$id."'".($selectedFunc($current->getTimestamp(), $id, $dow, $dom, $extra) ? " selected" : "").">".security::htmlsafe($type)."</option>";
      }

      echo "</td>";

      $start = false;
      $day++;
      $current->add($interval);
    }

    for ($i = $dow + 1; $i <= 7; $i++) {
      echo "<td></td>";
    }

    echo "</tr></table></div>";
  }
}
