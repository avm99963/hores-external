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

require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$day = schedules::getDay($id);

if ($day === false) {
  security::notFound();
}

$empty = [];

foreach (schedules::$allEvents as $date) {
  $empty[$date] = (intervals::measure([$day["begins".$date], $day["ends".$date]]) == 0);
}
?>

<form action="doeditdayschedule.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?=(int)$day["id"]?>">
  <h4 class="mdl-dialog__title">Modificar horario</h4>
  <div class="mdl-dialog__content">
    <h5>Día</h5>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select id="edit_day" class="mdlext-selectfield__select" disabled>
        <?php
        foreach (calendars::$days as $id => $tday) {
          echo '<option value="'.(int)$id.'"'.($day["day"] == $id ? " selected" : "").'>'.security::htmlsafe($tday).'</option>';
        }
        ?>
      </select>
      <label for="edit_day" class="mdlext-selectfield__label">Día de la semana</label>
    </div>
    <br>
    <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
      <select id="edit_type" class="mdlext-selectfield__select" disabled>
        <?php
        foreach (calendars::$types as $id => $type) {
          if ($id == calendars::TYPE_FESTIU) continue;
          echo '<option value="'.(int)$id.'"'.($day["typeday"] == $id ? " selected" : "").'>'.security::htmlsafe($type).'</option>';
        }
        ?>
      </select>
      <label for="edit_type" class="mdlext-selectfield__label">Tipo de día</label>
    </div>

    <h5>Jornada laboral</h5>
    <p>De <input type="time" name="beginswork" <?=(!$empty["work"] ? " value='".schedules::sec2time($day["beginswork"])."'" : "")?> required> a <input type="time" name="endswork" <?=(!$empty["work"] ? " value='".schedules::sec2time($day["endswork"])."'" : "")?> required></p>

    <h5>Desayuno</h5>
    <p>De <input type="time" name="beginsbreakfast" <?=(!$empty["breakfast"] ? " value='".schedules::sec2time($day["beginsbreakfast"])."'" : "")?>> a <input type="time" name="endsbreakfast" <?=(!$empty["breakfast"] ? " value='".schedules::sec2time($day["endsbreakfast"])."'" : "")?>></p>

    <h5>Comida</h5>
    <p>De <input type="time" name="beginslunch" <?=(!$empty["lunch"] ? " value='".schedules::sec2time($day["beginslunch"])."'" : "")?>> a <input type="time" name="endslunch" <?=(!$empty["lunch"] ? " value='".schedules::sec2time($day["endslunch"])."'" : "")?>></p>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary">Modificar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
