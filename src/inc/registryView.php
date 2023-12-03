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

class registryView {
  public static function renderRegistry(&$registry, &$companies, $scrollable = false, $showPersonAndCompany = true, $isForWorker = false, $isForValidationView = false) {
    global $conf, $renderRegistryAutoIncremental;
    if (!isset($renderRegistryAutoIncremental)) $renderRegistryAutoIncremental = 0;
    $menu = "";
    ?>
    <div class="mdl-shadow--2dp overflow-wrapper incidents-wrapper<?=($scrollable ? " incidents-wrapper--scrollable" : "")?>">
      <table class="incidents">
        <tr>
          <?php if ($conf["debug"]) { ?><th>ID</th><?php } ?>
          <th<?=($isForValidationView ? " class=\"has-checkbox\"" : "")?>>
            <?php if ($isForValidationView) {
              ?>
              <label for="checkboxall_r_<?=$renderRegistryAutoIncremental?>" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" data-check-all="true">
                <input type="checkbox" id="checkboxall_r_<?=$renderRegistryAutoIncremental?>" class="mdl-checkbox__input" autocomplete="off">
              </label>
              <?php
            }
            ?>
          </th>
          <?php
          if ($showPersonAndCompany) {
            ?>
            <th>Nombre</th>
            <th>Empresa</th>
            <?php
          }
          ?>
          <th>Día</th>
          <th>Jornada laboral</th>
          <th>Desayuno</th>
          <th>Comida</th>
          <th></th>
        </tr>
        <?php
        foreach ($registry as $record) {
          $id = (int)$record["id"]."_".(int)$renderRegistryAutoIncremental;
          $breakfastInt = [$record["beginsbreakfast"], $record["endsbreakfast"]];
          $lunchInt = [$record["beginslunch"], $record["endslunch"]];
          ?>
          <tr<?=($record["invalidated"] == 1 ? ' class="mdl-color-text--grey-700 line-through"' : '')?>>
            <?php if ($conf["debug"]) { ?><td><?=(int)$record["id"]?></td><?php } ?>
            <td class="icon-cell<?=($isForValidationView ? " has-checkbox" : "")?>">
              <?php
              if ($isForValidationView) {
                ?>
                <label for="checkbox_r_<?=$id?>" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                  <input type="checkbox" id="checkbox_r_<?=$id?>" data-record="<?=(int)$record["id"]?>" class="mdl-checkbox__input" autocomplete="off">
                </label>
                <?php
              } else {
                ?>
                <i id="state<?=$id?>" class="material-icons <?=security::htmlsafe(registry::$stateIconColors[$record["state"]])?>"><?=security::htmlsafe(registry::$stateIcons[$record["state"]])?></i>
                <?php
                visual::addTooltip("state".$id, security::htmlsafe(registry::$stateTooltips[$record["state"]]));
              }
              ?>
            </td>
            <?php
            if ($showPersonAndCompany) {
              ?>
              <td class="can-strike"><span<?=($isForWorker ? '' : ' data-dyndialog-href="dynamic/user.php?id='.(int)$record["personid"].'"')?>><?=security::htmlsafe($record["workername"])?></span></td>
              <td class="can-strike"><?=security::htmlsafe($companies[$record["companyid"]])?></td>
              <?php
            }
            ?>
            <td class="can-strike"><?=date::getShortDate($record["day"])?></td>
            <td class="centered can-strike"><?=schedules::sec2time($record["beginswork"])." - ".schedules::sec2time($record["endswork"])?></td>
            <td class="centered can-strike"><?=(intervals::measure($breakfastInt) == 0 ? "-" : ($isForWorker ? export::sec2hours(intervals::measure($breakfastInt)) : schedules::sec2time($record["beginsbreakfast"])." - ".schedules::sec2time($record["endsbreakfast"])))?></td>
            <td class="centered can-strike"><?=(intervals::measure($lunchInt) == 0 ? "-" : ($isForWorker ? export::sec2hours(intervals::measure($lunchInt)) : schedules::sec2time($record["beginslunch"])." - ".schedules::sec2time($record["endslunch"])))?></td>
            <td><button id="actions<?=$id?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect custom-actions-btn"><i class="material-icons icon">more_vert</i></button></td>
          </tr>
          <?php
          $menu .= '<ul class="mdl-menu mdl-menu--unaligned mdl-js-menu mdl-js-ripple-effect" for="actions'.$id.'">';

          if (!$isForWorker && $record["invalidated"] == 0) {
            $menu .= '<a href="dynamic/invalidaterecord.php?id='.(int)$record["id"].'" data-dyndialog-href="dynamic/invalidaterecord.php?id='.(int)$record["id"].'"><li class="mdl-menu__item">Invalidar</li></a>';
          }

          $menu .= '<a href="dynamic/authorsrecord.php?id='.(int)$record["id"].'" data-dyndialog-href="dynamic/authorsrecord.php?id='.(int)$record["id"].'"><li class="mdl-menu__item">Autoría</li></a></ul>';

          $renderRegistryAutoIncremental++;
        }
        ?>
      </table>
      <?php echo $menu; ?>
    </div>
  <?php
  }
}
