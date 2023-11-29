<?php
class helpView {
  public static function renderHelpButton($place, $topRight = false, $margin = false) {
    $url = help::get($place);
    if ($url === false) return;

    echo ($topRight ? '<div class="help-btn--top-right'.($margin ? ' help-btn--top-right-margin': '').'">' : '').'<a href="'.security::htmlsafe($url).'" target="_blank" rel="noopener noreferrer" class="mdl-button mdl-button--colored mdl-button-js mdl-button--icon mdl-js-ripple-effect" id="help'.(int)$place.'"><i class="material-icons">help_outline</i><span class="mdl-ripple"></span></a>'.($topRight ? '</div>' : '');
    echo '<div class="mdl-tooltip" for="help'.(int)$place.'">Ayuda</div>';
  }
}
