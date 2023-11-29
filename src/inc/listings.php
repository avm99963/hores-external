<?php
class listings {
  public static function renderFilterDialog($form, $select) {
    global $_GET;
    ?>
    <dialog class="mdl-dialog" id="filter">
      <form action="<?=$form?>" method="GET" enctype="multipart/form-data">
        <h4 class="mdl-dialog__title">Filtrar lista</h4>
        <div class="mdl-dialog__content">
          <h5>Categorías</h5>
          <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="cat-1">
            <input type="checkbox" id="cat-1" name="categories[-1]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["categories"] && in_array(-1, $select["selected"]["categories"])) ? " checked" : "")?>>
            <span class="mdl-checkbox__label">Sin categoría</span>
          </label>
          <?php
          foreach (categories::getAll(false, false, true) as $c) {
            $haschilds = (count($c["childs"]) > 0);
            if ($haschilds) {
              $subcategories_arr = [];
              foreach ($c["childs"] as $child) {
                $subcategories_arr[] = "&ldquo;".security::htmlsafe($child["name"])."&rdquo;";
              }
              $subcategories = implode(", ", $subcategories_arr);
            }
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="cat<?=(int)$c["id"]?>">
              <input type="checkbox" id="cat<?=(int)$c["id"]?>" name="categories[<?=(int)$c["id"]?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["categories"] && in_array($c["id"], $select["selected"]["categories"])) ? " checked" : "")?>>
              <span class="mdl-checkbox__label"><?=security::htmlsafe($c["name"])?> <?php if ($haschilds) { ?><i id="haschilds<?=(int)$c["id"]?>" class="material-icons help">info</i><?php } ?></span>
            </label>
            <?php
            if ($haschilds) {
              ?>
              <div class="mdl-tooltip" for="haschilds<?=(int)$c["id"]?>">Esta categoría incluye la<?=(count($c["childs"]) == 1 ? "" : "s")?> subcategoría<?=(count($c["childs"]) == 1 ? "" : "s")?> <?=$subcategories?></div>
              <?php
            }
          }
          ?>
          <h5>Empresas</h5>
          <?php
          foreach (companies::getAll() as $id => $company) {
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="comp<?=(int)$id?>">
              <input type="checkbox" id="comp<?=(int)$id?>" name="companies[<?=(int)$id?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["companies"] && in_array($id, $select["selected"]["companies"])) ? " checked" : "")?>>
              <span class="mdl-checkbox__label"><?=security::htmlsafe($company)?></span>
            </label>
            <?php
          }
          ?>
          <h5>Tipos de usuario</h5>
          <?php
          foreach (security::$types as $id => $type) {
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="type<?=(int)$id?>">
              <input type="checkbox" id="type<?=(int)$id?>" name="types[<?=(int)$id?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["types"] && in_array($id, $select["selected"]["types"])) ? " checked" : "")?>>
              <span class="mdl-checkbox__label"><?=security::htmlsafe($type)?></span>
            </label>
            <?php
          }

          if ($form == "workers.php") {
            echo "<h5>Horario actual</h5>";
            foreach (schedules::$workerScheduleStatusShort as $id => $type) {
              ?>
              <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="schedulesstatus<?=(int)$id?>">
                <input type="checkbox" id="schedulesstatus<?=(int)$id?>" name="schedulesstatus[<?=(int)$id?>]" class="mdl-checkbox__input" value="1"<?=(isset($_GET["schedulesstatus"][(int)$id]) ? " checked" : "")?>>
                <span class="mdl-checkbox__label mdl-color-text--<?=security::htmlsafe(schedules::$workerScheduleStatusColors[$id])?>"><?=security::htmlsafe($type)?></span>
              </label>
              <?php
            }
          }
          ?>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Filtrar</button>
          <button onclick="event.preventDefault(); document.querySelector('#filter').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
        </div>
      </form>
    </dialog>
    <?php
  }

  public static function buildSelect($form) {
    global $select, $selectedSchedulesStatus, $_GET;

    $select = array("enabled" => [], "selected" => []);

    foreach (people::$filters as $f) {
      $select["enabled"][$f] = isset($_GET[$f]);
      if ($select["enabled"][$f]) {
        $select["selected"][$f] = (isset($_GET[$f]) ? array_keys($_GET[$f]) : []);
      }
    }

    if ($form == "workers.php") {
      if (isset($_GET["schedulesstatus"]) && is_array($_GET["schedulesstatus"]) && count($_GET["schedulesstatus"])) {
        $selectedSchedulesStatus = array_keys($_GET["schedulesstatus"]);
      } else {
        $selectedSchedulesStatus = false;
      }
    }
  }
}
