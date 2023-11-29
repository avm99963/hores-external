<?php
class schedulesView {
  const HOUR_HEIGHT = 30;

  public static function renderSchedule(&$week, $editEnabled = false, $editLink, $deleteLink) {
    $secmin = 24*60*60;
    $secmax = 0;

    foreach ($week as $day) {
      if ($day["beginswork"] < $secmin) $secmin = $day["beginswork"];
      if ($day["endswork"] > $secmax) $secmax = $day["endswork"];
    }

    $min = max(floor($secmin/(60*60)) - 1, 0);
    $max = min(ceil($secmax/(60*60)) + 1, 24);
    ?>
    <div class="overflow-wrapper overflow-wrapper--for-table" style="text-align: center;">
      <div class="schedule mdl-shadow--2dp">
        <div class="sidetime">
          <?php
          for ($hour = $min; $hour <= $max; $hour++) {
            ?>
            <div class="hour">
              <div class="hour--text"><?=security::htmlsafe(visual::padNum($hour, 2).":00")?></div>
            </div>
            <?php
          }
          ?>
        </div>
        <?php
        for ($day = 0; $day < 7; $day++) {
          $isset = isset($week[$day]);

          if (!$isset && $day >= 5 && !isset($week[5]) && !isset($week[6])) continue;

          if ($isset) {
            $size = [];
            $size["work"] = [];
            $size["work"]["top"] = (($week[$day]["beginswork"]/(60*60)) - $min)*self::HOUR_HEIGHT;
            $size["work"]["height"] = intervals::measure([$week[$day]["beginswork"], $week[$day]["endswork"]])/(60*60)*self::HOUR_HEIGHT;

            foreach (schedules::$otherEvents as $event) {
              if (intervals::measure([$week[$day]["begins".$event], $week[$day]["ends".$event]]) > 0) {
                $size[$event] = [];
                $size[$event]["top"] = ($week[$day]["begins".$event] - $week[$day]["beginswork"])/(60*60)*self::HOUR_HEIGHT;
                $size[$event]["height"] = intervals::measure([$week[$day]["begins".$event], $week[$day]["ends".$event]])/(60*60)*self::HOUR_HEIGHT;
              }
            }
          }
          ?>
          <div class="day">
            <div class="day--header"><?=security::htmlsafe(calendars::$days[$day])?></div>
            <div class="day--content">
              <?php
              if ($isset) {
                ?>
                <div class="work-event" style="top: <?=(int)round($size["work"]["top"])?>px; height: <?=(int)round($size["work"]["height"])?>px;">
                  <?php
                  if ($editEnabled) {
                    ?>
                    <div class="event--actions">
                      <a href='<?=$editLink($week[$day])?>' data-dyndialog-href='<?=$editLink($week[$day])?>' title='Modificar horario'><i class='material-icons'>edit</i></a>
                      <a href='<?=$deleteLink($week[$day])?>' data-dyndialog-href='<?=$deleteLink($week[$day])?>' title='Eliminar horario'><i class='material-icons'>delete</i></a>
                    </div>
                    <?php
                  }
                  ?>
                  <div class="event--header">Trabajo</div>
                  <div class="event--body"><?=security::htmlsafe(schedules::sec2time($week[$day]["beginswork"]))?> - <?=security::htmlsafe(schedules::sec2time($week[$day]["endswork"]))?></div>
                  <?php
                  foreach (schedules::$otherEvents as $event) {
                    if (isset($size[$event])) {
                      ?>
                      <div class="inline-event" style="top: <?=(int)round($size[$event]["top"])?>px; height: <?=(int)round($size[$event]["height"])?>px;">
                        <div class="event--header"><?=security::htmlsafe(schedules::$otherEventsDescription[$event])?></div>
                        <div class="event--body"></div>
                      </div>
                      <?php
                    }
                  }
                  ?>
                </div>
                <?php
              }

              for ($hour = $min; $hour <= $max; $hour++) {
                ?>
                <div class="hour"></div>
                <?php
              }
              ?>
            </div>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
  <?php
  }

  public static function renderPlainSchedule($week, $editEnabled = false, $editLink, $deleteLink, &$flag) {
    foreach ($week as $day) {
      $flag = true;
      ?>
      <h4><?=security::htmlsafe(calendars::$days[$day["day"]])?> <span class="mdl-color-text--grey-600">(<?=security::htmlsafe(calendars::$types[$day["typeday"]])?>)</span>
        <?php
        if ($editEnabled) {
          ?>
          <a href='<?=$editLink($day)?>' data-dyndialog-href='<?=$editLink($day)?>' title='Modificar horario' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect'><i class='material-icons icon'>edit</i></a>
          <a href='<?=$deleteLink($day)?>' data-dyndialog-href='<?=$deleteLink($day)?>' title='Eliminar horario' class='mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect'><i class='material-icons icon'>delete</i></a>
          <?php
        }
        ?>
      </h4>
      <ul>
        <li><b>Horario de trabajo:</b> <?=security::htmlsafe(schedules::sec2time($day["beginswork"]))?> - <?=security::htmlsafe(schedules::sec2time($day["endswork"]))?></li>
        <?php if (intervals::measure([$day["beginsbreakfast"], $day["endsbreakfast"]]) > 0) { ?><li><b>Desayuno:</b> <?=security::htmlsafe(schedules::sec2time($day["beginsbreakfast"]))?> - <?=security::htmlsafe(schedules::sec2time($day["endsbreakfast"]))?></li><?php } ?>
        <?php if (intervals::measure([$day["beginslunch"], $day["endslunch"]]) > 0) { ?><li><b>Comida:</b> <?=security::htmlsafe(schedules::sec2time($day["beginslunch"]))?> - <?=security::htmlsafe(schedules::sec2time($day["endslunch"]))?></li><?php } ?>
      </ul>
      <?php
    }
  }
}
