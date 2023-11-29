<?php
class incidentsView {
  public static $limitOptions = [10, 15, 20, 30, 40, 50];

  public static $incidentsMsgs = [
    ["added", "Se ha añadido la incidencia correctamente."],
    ["modified", "Se ha modificado la incidencia correctamente."],
    ["removed", "Se ha eliminado la incidencia correctamente."],
    ["invalidated", "Se ha invalidado la incidencia correctamente."],
    ["empty", "Faltan datos por introducir en el formulario."],
    ["unexpected", "Ha ocurrido un error inesperado. Inténtelo de nuevo en unos segundos."],
    ["cannotmodify", "No se pueden modificar la incidencia porque ya ha sido registrada o se ha invalidado manualmente."],
    ["verified1", "Se ha verificado la incidencia correctamente."],
    ["verified0", "Se ha rechazado la incidencia correctamente."],
    ["overlap", "La incidencia no se ha añadido porque se solapa con otra incidencia del mismo trabajador."],
    ["order", "La hora de inicio debe ser anterior a la hora de fin."],
    ["addedemailnotsent", "La incidencia se ha añadido, pero no se ha podido enviar un correo de notificación a los responsables de la categoría del trabajador. Por favor, notifica a estas personas manualmente."],
    ["filesize", "El tamaño del archivo adjunto es demasiado grande (el límite es de ".files::READABLE_MAX_SIZE.")"],
    ["filetype", "El formato del archivo no está soportado."],
    ["attachmentadded", "Se ha añadido el archivo adjunto correctamente."],
    ["attachmentdeleted", "Se ha eliminado el archivo adjunto correctamente."],
    ["addedrecurring", "Se han añadido todas las incidencias pertinentes correctamente."],
    ["unexpectedrecurring", "Ha habido algún problema añadiendo alguna(s) o todas las incidencias que se tenían que crear."],
    ["addednotautovalidated", "La incidencia se ha añadido, pero no se ha podido autovalidar."],
    ["deleteincidentsbulksuccess", "Las incidencias se han eliminado/invalidado correctamente."],
    ["deleteincidentsbulkpartialsuccess", "Algunas incidencias (o todas) no se han podido eliminar/invalidar. Por favor, comprueba el resultado de la acción."]
  ];

  public static function renderIncidents(&$incidents, &$companies, $scrollable = false, $showPersonAndCompany = true, $isForWorker = false, $isForValidationView = false, $isForMassEdit = false, $continueUrl = "incidents.php") {
    global $conf, $renderIncidentsAutoIncremental;
    if (!isset($renderIncidentsAutoIncremental)) $renderIncidentsAutoIncremental = 0;
    $menu = "";

    $safeContinueUrl = security::htmlsafe(urlencode($continueUrl));

    if ($isForMassEdit) {
      ?>
      <div class="left-actions">
        <button id="deleteincidentsbulk" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons icon">delete</i></button>
      </div>
      <?php
    }
    ?>
    <div class="mdl-shadow--2dp overflow-wrapper incidents-wrapper<?=($scrollable ? " incidents-wrapper--scrollable" : "")?>">
      <table class="incidents">
        <?php
        if ($isForValidationView || $isForMassEdit) {
          ?>
          <tr class="artificial-height">
            <?php if ($conf["debug"]) echo "<td></td>"; ?>
            <td class="icon-cell has-checkbox">
              <label for="checkboxall_i_<?=$renderIncidentsAutoIncremental?>" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" data-check-all="true">
                <input type="checkbox" id="checkboxall_i_<?=$renderIncidentsAutoIncremental?>" class="mdl-checkbox__input" autocomplete="off">
              </label>
            </td>
            <?php if ($conf["debug"]) echo "<td></td>"; ?>
            <td></td>
            <?php if (!$isForValidationView) echo "<td></td>"; ?>
            <td></td>
            <?php if ($showPersonAndCompany) echo "<td></td><td></td>"; ?>
            <td></td>
          </tr>
          <?php
        }

        foreach ($incidents as $incident) {
          $id = (int)$incident["id"]."_".(int)$renderIncidentsAutoIncremental;
          $canEdit = (!$isForWorker && !in_array($incident["state"], incidents::$cannotEditStates)) || ($isForWorker && in_array($incident["state"], incidents::$workerCanEditStates));
          $canRemove = (!$isForWorker && in_array($incident["state"], incidents::$canRemoveStates)) || ($isForWorker && in_array($incident["state"], incidents::$workerCanRemoveStates));
          $canInvalidate = !$isForWorker && in_array($incident["state"], incidents::$canInvalidateStates);
          $attachments = count(incidents::getAttachmentsFromIncident($incident));
          ?>
          <tr<?=(in_array($incident["state"], incidents::$invalidStates) ? ' class="mdl-color-text--grey-700 line-through"' : '')?>>
            <?php if ($conf["debug"]) { ?><td><?=(int)$incident["id"]?></td><?php } ?>
            <?php
            if ($isForValidationView || $isForMassEdit) {
              ?>
              <td class="icon-cell has-checkbox">
                <label for="checkbox_i_<?=$id?>" class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect">
                  <input type="checkbox" id="checkbox_i_<?=$id?>" data-incident="<?=(int)$incident["id"]?>" class="mdl-checkbox__input"<?=($isForMassEdit && !in_array($incident["state"], incidents::$canRemoveStates) && !in_array($incident["state"], incidents::$canInvalidateStates) ? " disabled" : "")?> autocomplete="off">
                </label>
              </td>
              <?php
            }

            if (!$isForValidationView) {
              ?>
              <td class="icon-cell">
                <i id="state<?=$id?>" class="material-icons <?=security::htmlsafe(incidents::$stateIconColors[$incident["state"]])?>"><?=security::htmlsafe(incidents::$stateIcons[$incident["state"]])?></i>
              </td>
              <?php
              visual::addTooltip("state".$id, security::htmlsafe(incidents::$stateTooltips[$incident["state"]]));
            }
            ?>
            <td class="can-strike"><?=security::htmlsafe($incident["typename"])?></td>
            <?php if ($showPersonAndCompany) {
              ?>
              <td class="can-strike"><span data-dyndialog-href="dynamic/user.php?id=<?=(int)$incident["personid"]?>"><?=security::htmlsafe($incident["workername"])?></span></td>
              <td class="can-strike"><?=security::htmlsafe($companies[$incident["companyid"]])?></td>
              <?php
            }
            ?>
            <td class="can-strike"><?=strftime("%d %b %Y", $incident["day"])." ".security::htmlsafe($incident["allday"] ? "(todo el día)" : schedules::sec2time($incident["begins"])."-".schedules::sec2time($incident["ends"]))?></td>
            <td>
              <a href="dynamic/editincidentcomment.php?id=<?=(int)$incident["id"]?>&continue=<?=$safeContinueUrl?>" data-dyndialog-href="dynamic/editincidentcomment.php?id=<?=(int)$incident["id"]?>&continue=<?=$safeContinueUrl?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" title="Ver/editar las observaciones"><i class="material-icons icon"><?=(empty($incident["details"]) && empty($incident["workerdetails"]) ? "mode_" : "")?>comment</i></a>
              <span<?=($attachments > 0 ? ' class="mdl-badge mdl-badge--overlap" data-badge="'.$attachments.'"' : '')?>><a href="dynamic/incidentattachments.php?id=<?=(int)$incident["id"]?>&continue=<?=$safeContinueUrl?>" data-dyndialog-href="dynamic/incidentattachments.php?id=<?=(int)$incident["id"]?>&continue=<?=$safeContinueUrl?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" title="Ver/gestionar los archivos adjuntos"><i class="material-icons icon">attach_file</i></a></span>
              <button id="actions<?=$id?>" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect custom-actions-btn"><i class="material-icons icon">more_vert</i></button>
              <?php
              $menu .= '<ul class="mdl-menu mdl-menu--unaligned mdl-js-menu mdl-js-ripple-effect" for="actions'.$id.'">';

              if ($canEdit) $menu .= '<a href="dynamic/editincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'" data-dyndialog-href="dynamic/editincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'"><li class="mdl-menu__item">Editar</li></a>';

              if ($canRemove) $menu .= '<a href="dynamic/deleteincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'" data-dyndialog-href="dynamic/deleteincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'"><li class="mdl-menu__item">Eliminar</li></a>';

              if ($canInvalidate) $menu .= '<a href="dynamic/invalidateincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'" data-dyndialog-href="dynamic/invalidateincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'"><li class="mdl-menu__item">Invalidar</li></a>';

              $menu .= '<a href="dynamic/authorsincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'" data-dyndialog-href="dynamic/authorsincident.php?id='.(int)$incident["id"].'&continue='.$safeContinueUrl.'"><li class="mdl-menu__item">Autoría</li></a></ul>';

              if ($incident["state"] == incidents::STATE_UNVERIFIED && !$isForWorker) {
                ?>
                <form action="doverifyincident.php" method="POST" class="verification-actions">
                  <input type="hidden" name="id" value="<?=(int)$incident["id"]?>">
                  <?php visual::addContinueInput($continueUrl); ?>
                  <button name="value" value="1" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons mdl-color-text--green">check</i></button><button name="value" value="0" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect"><i class="material-icons mdl-color-text--red">close</i></button>
                </form>
                <?php
              }
              ?>
            </td>
          </tr>
          <?php
          $renderIncidentsAutoIncremental++;
        }
        ?>
      </table>
      <?php echo $menu; ?>
    </div>
  <?php
  }

  public static function renderIncidentForm(&$workers, $valueFunction, $textFunction, &$companies, $isForWorker = false, $recurrent = false, $continueUrl = false) {
    $prefix = ($recurrent ? "recurring" : "");
    ?>
    <script>
    window.addEventListener("load", e1 => {
      document.getElementById("<?=$prefix?>allday").addEventListener("change", e => {
        var partialtime = document.getElementById("<?=$prefix?>partialtime");
        if (e.target.checked) {
          partialtime.classList.add("notvisible");
        } else {
          partialtime.classList.remove("notvisible");
        }
      });
      <?php
      if ($recurrent) {
        ?>
        var defaultFields = [
          {
            "name": "day",
            "min": 0,
            "max": 4
          },
          {
            "name": "type",
            "min": 1,
            "max": 2
          }
        ];

        defaultFields.forEach(field => {
          for (var i = field.min; i <= field.max; i++) {
            document.querySelector("[for=\""+field.name+"-"+i+"\"]").MaterialCheckbox.check();

            var checkbox = document.getElementById(field.name+"-"+i);
            checkbox.checked = true;
            if ("createEvent" in document) {
              var evt = document.createEvent("HTMLEvents");
              evt.initEvent("change", false, true);
              checkbox.dispatchEvent(evt);
            }
          }
        });
        <?php
      }
      ?>
    });
    </script>
    <dialog class="mdl-dialog" id="add<?=$prefix?>incident">
      <form action="doadd<?=$prefix?>incident.php" method="POST" autocomplete="off">
        <?php
        if ($continueUrl !== false) visual::addContinueInput($continueUrl);
        helpView::renderHelpButton(help::PLACE_INCIDENT_FORM, true, true);
        ?>
        <h4 class="mdl-dialog__title">Añade una incidencia<?=($recurrent ? " recurrente" : "")?></h4>
        <div class="mdl-dialog__content">
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="worker" id="<?=$prefix?>worker" class="mdlext-selectfield__select" data-required>
              <option></option>
              <?php
              foreach ($workers as $worker) {
                if ($worker["hidden"] == 1) continue;
                echo '<option value="'.security::htmlsafe($valueFunction($worker, $companies)).'">'.security::htmlsafe($textFunction($worker, $companies)).'</option>';
              }
              ?>
            </select>
            <label for="<?=$prefix?>worker" class="mdlext-selectfield__label">Trabajador</label>
          </div>
          <br>
          <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
            <select name="type" id="<?=$prefix?>type" class="mdlext-selectfield__select" data-required>
              <option></option>
              <?php
              foreach (incidents::getTypesForm($isForWorker) as $i) {
                echo '<option value="'.(int)$i["id"].'">'.security::htmlsafe($i["name"]).'</option>';
              }
              ?>
            </select>
            <label for="<?=$prefix?>type" class="mdlext-selectfield__label">Tipo</label>
          </div>

          <?php
          if ($isForWorker) {
            echo "<p>Para usar un tipo de incidencia que no esté en la lista, debes ponerte en contacto con RRHH para que rellenen ellos la incidencia manualmente.</p>";
          }
          ?>

          <?php
          if ($recurrent) {
            ?>
            <h5>Recurrencia</h5>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="date" name="firstday" id="<?=$prefix?>firstday" autocomplete="off" data-required>
              <label class="mdl-textfield__label always-focused" for="<?=$prefix?>firstday">Día de inicio</label>
            </div>
            <br>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="date" name="lastday" id="<?=$prefix?>lastday" autocomplete="off" data-required>
              <label class="mdl-textfield__label always-focused" for="<?=$prefix?>lastday">Día de fin</label>
            </div>
            <br>
            <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
              <div id="dayMenu" class="mdlext-selectfield__select mdl-custom-selectfield__select" tabindex="0">-</div>
              <ul class="mdl-menu mdl-menu--bottom mdl-js-menu mdl-custom-multiselect mdl-custom-multiselect-js" for="dayMenu">
                <?php
                foreach (calendars::$days as $id => $day) {
                  ?>
                  <li class="mdl-menu__item mdl-custom-multiselect__item">
                    <label class="mdl-checkbox mdl-js-checkbox" for="day-<?=(int)$id?>">
                      <input type="checkbox" id="day-<?=(int)$id?>" name="day[]" value="<?=(int)$id?>" data-value="<?=(int)$id?>" class="mdl-checkbox__input">
                      <span class="mdl-checkbox__label"><?=security::htmlsafe($day)?></span>
                    </label>
                  </li>
                  <?php
                }
                ?>
              </ul>
              <label for="day" class="mdlext-selectfield__label always-focused mdl-color-text--primary">Día de la semana</label>
            </div>
            <br>
            <div class="mdlext-selectfield mdlext-js-selectfield mdlext-selectfield--floating-label">
              <div id="dayType" class="mdlext-selectfield__select mdl-custom-selectfield__select" tabindex="0">-</div>
              <ul class="mdl-menu mdl-menu--bottom mdl-js-menu mdl-custom-multiselect mdl-custom-multiselect-js" for="dayType">
                <?php
                foreach (calendars::$types as $id => $type) {
                  if ($id == calendars::TYPE_FESTIU) continue;
                  ?>
                  <li class="mdl-menu__item mdl-custom-multiselect__item">
                    <label class="mdl-checkbox mdl-js-checkbox" for="type-<?=(int)$id?>">
                      <input type="checkbox" id="type-<?=(int)$id?>" name="daytype[]" value="<?=(int)$id?>" data-value="<?=(int)$id?>" class="mdl-checkbox__input">
                      <span class="mdl-checkbox__label"><?=security::htmlsafe($type)?></span>
                    </label>
                  </li>
                  <?php
                }
                ?>
              </ul>
              <label for="day" class="mdlext-selectfield__label always-focused mdl-color-text--primary">Tipo de día</label>
            </div>
            <h5>Afectación</h5>
            <?php
          } else {
            ?>
            <h5>Afectación</h5>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
              <input class="mdl-textfield__input" type="date" name="day" id="<?=$prefix?>day" autocomplete="off" data-required>
              <label class="mdl-textfield__label always-focused" for="<?=$prefix?>day">Día</label>
            </div>
            <br>
            <?php
          }
          ?>
          <p>
            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?=$prefix?>allday">
              <input type="checkbox" id="<?=$prefix?>allday" name="allday" value="1" class="mdl-switch__input">
              <span class="mdl-switch__label">Día entero</span>
            </label>
          </p>
          <div id="<?=$prefix?>partialtime">De <input type="time" name="begins"> a <input type="time" name="ends"></div>

          <h5>Detalles adicionales</h5>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <textarea class="mdl-textfield__input" name="details" id="<?=$prefix?>details"></textarea>
            <label class="mdl-textfield__label" for="<?=$prefix?>details">Observaciones (opcional)</label>
          </div>
          <?php
          if (!$isForWorker && !$recurrent) {
            ?>
            <p>
              <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect" for="<?=$prefix?>autoverify">
                <input type="checkbox" id="<?=$prefix?>autoverify" name="autoverify" value="1" class="mdl-switch__input" checked>
                <span class="mdl-switch__label">Saltar la cola de revisión</span>
              </label>
            </p>
            <?php
          }

          if (!$isForWorker) echo "<p>Las observaciones aparecerán en los PDFs que se exporten.</p>";
          else echo "<p>Las observaciones serán únicamente visibles para los administradores del sistema.</p><p>Al añadir la incidencia, se guardará tu <a href=\"https://help.gnome.org/users/gnome-help/stable/net-what-is-ip-address.html.es\" target=\"_blank\" rel=\"noopener noreferrer\">dirección IP</a> y la fecha y hora actual para autovalidar la incidencia.</p>";
          if (!$recurrent) echo '<p>Después de crear la incidencia podrás añadir archivos adjuntos haciendo clic en el botón <i class="material-icons" style="vertical-align: middle;">attach_file</i>.</p>';
          ?>
        </div>
        <div class="mdl-dialog__actions">
          <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent">Añadir</button>
          <button onclick="event.preventDefault(); document.querySelector('#add<?=$prefix?>incident').close();" class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
        </div>
      </form>
    </dialog>
    <?php
  }

  public static function renderFilterDialog($select) {
    global $_GET;
    ?>
    <style>
    #filter {
      max-width: 300px;
      width: auto;
    }

    #filter .mdl-checkbox {
      height: auto;
    }
    </style>
    <dialog class="mdl-dialog" id="filter">
      <form action="incidents.php" method="GET" enctype="multipart/form-data">
        <h4 class="mdl-dialog__title">Filtrar lista</h4>
        <div class="mdl-dialog__content">
          <h5>Por fecha</h5>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="date" name="begins" id="begins" autocomplete="off" <?=$select["enabled"]["begins"] ? " value=\"".security::htmlsafe($select["selected"]["begins"])."\"" : ""?>>
            <label class="mdl-textfield__label always-focused" for="begins">Fecha inicio (opcional)</label>
          </div>
          <br>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="date" name="ends" id="ends" autocomplete="off" <?=$select["enabled"]["ends"] ? " value=\"".security::htmlsafe($select["selected"]["ends"])."\"" : ""?>>
            <label class="mdl-textfield__label always-focused" for="ends">Fecha fin (opcional)</label>
          </div>
          <h5>Por estado <i id="tt_incidentstatusnotpaginated" class="material-icons help">info</i></h5>
          <div class="mdl-tooltip" for="tt_incidentstatusnotpaginated">Al filtrar por estado, la página de resultados no estará paginada y saldrán todos los resultados en una misma página. La acción podría consumir bastantes recursos y tomar más tiempo del habitual.</div>
          <?php
          foreach (incidents::$statesOrderForFilters as $id) {
            ?>
            <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="state<?=(int)$id?>">
              <input type="checkbox" id="state<?=(int)$id?>" name="states[<?=(int)$id?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["states"] && in_array($id, $select["selected"]["states"])) ? " checked" : "")?>>
              <span class="mdl-checkbox__label"><?=security::htmlsafe(incidents::$stateTooltips[$id])?></span>
            </label>
            <?php
          }
          ?>
          <h5>Por tipo</h5>
          <?php
          $types = incidents::getTypes();
          if ($types !== false) {
            foreach ($types as $type) {
              ?>
              <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="type<?=(int)$type["id"]?>">
                <input type="checkbox" id="type<?=(int)$type["id"]?>" name="types[<?=(int)$type["id"]?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"]["types"] && in_array($type["id"], $select["selected"]["types"])) ? " checked" : "")?>>
                <span class="mdl-checkbox__label"><?=security::htmlsafe($type["name"])?></span>
              </label>
              <?php
            }
          }
          ?>
          <h5>Miscelánea</h5>
          <?php
          foreach (incidents::$filtersSwitchHelper as $f => $helper) {
            foreach (incidents::$filtersSwitchOptions as $value => $option) {
              ?>
              <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="<?=security::htmlsafe($f).(int)$value?>">
                <input type="checkbox" id="<?=security::htmlsafe($f).(int)$value?>" name="<?=security::htmlsafe($f)?>[<?=(int)$value?>]" class="mdl-checkbox__input" value="1"<?=(($select["enabled"][$f] && in_array($value, $select["selected"][$f])) ? " checked" : "")?>>
                <span class="mdl-checkbox__label"><?=security::htmlsafe($option." ".$helper)?></span>
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

  public static function buildSelect() {
    global $select, $_GET;

    $select = array("showPendingQueue" => false, "isAnyEnabled" => false, "pageUrl" => "", "pageUrlHasParameters" => false, "showResultsPaginated" => true, "enabled" => [], "selected" => []);

    $parameters = [];

    foreach (incidents::$filters as $f) {
      $fType = incidents::$filtersType[$f];

      switch ($fType) {
        case incidents::FILTER_TYPE_ARRAY:
        $select["enabled"][$f] = isset($_GET[$f]);
        break;

        case incidents::FILTER_TYPE_INT:
        $select["enabled"][$f] = isset($_GET[$f]) && $_GET[$f] !== "";
        break;

        case incidents::FILTER_TYPE_STRING:
        $select["enabled"][$f] = isset($_GET[$f]) && !empty($_GET[$f]);
        break;
      }

      if ($select["enabled"][$f]) {
        switch ($fType) {
          case incidents::FILTER_TYPE_ARRAY:
          $select["selected"][$f] = (isset($_GET[$f]) ? array_keys($_GET[$f]) : []);
          foreach ($select["selected"][$f] as $value) {
            $parameters[] = urlencode($f)."[".urlencode($value)."]=1";
          }
          break;

          case incidents::FILTER_TYPE_INT:
          $select["selected"][$f] = (int)$_GET[$f];
          $parameters[] = urlencode($f)."=".(int)$_GET[$f];
          break;

          case incidents::FILTER_TYPE_STRING:
          $select["selected"][$f] = $_GET[$f];
          $parameters[] = urlencode($f)."=".urlencode($_GET[$f]);
          break;
        }
      }
    }

    foreach ($select["enabled"] as $enabled) {
      if ($enabled) {
        $select["isAnyEnabled"] = true;
        break;
      }
    }

    if (!$select["isAnyEnabled"] || (isset($_GET["forceQueue"]) && $_GET["forceQueue"] == "1")) $select["showPendingQueue"] = true;

    $select["pageUrlHasParameters"] = (count($parameters) > 0);
    $select["pageUrl"] = "incidents.php".($select["pageUrlHasParameters"] ? "?".implode("&", $parameters) : "");
    $select["showResultsPaginated"] = !$select["enabled"]["states"];
  }

  public static function handleIncidentShortcuts() {
    global $_GET;
    if (isset($_GET["goTo"])) {
      switch ($_GET["goTo"]) {
        case "today":
        security::go("incidents.php?page=".(int)incidents::todayPage());
        break;
      }
    }
  }
}
