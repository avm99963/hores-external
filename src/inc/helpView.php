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

class helpView {
  public static function renderHelpButton($place, $topRight = false, $margin = false) {
    $url = help::get($place);
    if ($url === false) return;

    echo ($topRight ? '<div class="help-btn--top-right'.($margin ? ' help-btn--top-right-margin': '').'">' : '').'<a href="'.security::htmlsafe($url).'" target="_blank" rel="noopener noreferrer" class="mdl-button mdl-button--colored mdl-button-js mdl-button--icon mdl-js-ripple-effect" id="help'.(int)$place.'"><i class="material-icons">help_outline</i><span class="mdl-ripple"></span></a>'.($topRight ? '</div>' : '');
    echo '<div class="mdl-tooltip" for="help'.(int)$place.'">Ayuda</div>';
  }
}
