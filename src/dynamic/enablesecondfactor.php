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
security::checkType(security::WORKER, security::METHOD_NOTFOUND);
security::checkWorkerUIEnabled();
secondFactor::checkAvailability();

if (secondFactor::isEnabled()) {
  security::notFound();
}

$secret = secondFactor::generateSecret();
$url = "otpauth://totp/".str_replace("+", "%20", urlencode($conf["appName"])).":".urlencode(people::userData('username'))."?secret=".urlencode($secret)."&issuer=".str_replace("+", "%20", urlencode($conf["appName"]));
?>

<style>
#dynDialog {
  max-width: 500px;
  width: auto;
}

.step {
  padding: 10px 0;
  border-bottom: 1px solid #ebebeb;
}

.step .number {
  display: inline-block;
  vertical-align: middle;
  font-family: "Arial", sans-serif;
  font-size: 36px;
  font-weight: bold;
  color: green;
  margin: 0;
  margin-right: 15px;
  padding: 0;
  line-height: normal;
}

.step .text {
  display: inline-block;
  vertical-align: middle;
  margin: 0;
  padding: 0;
  width: Calc(100% - 40px);
}

.step .icon_container {
  float: right;
  height: 24px;
  padding-top: 9px;
  padding-right: 9px;
}

#qrcode {
  margin: 8px 0;
}

#qrcode img, #qrcode canvas {
  margin: auto;
}
</style>

<dynscript>
new QRCode(document.getElementById("qrcode"), {
  text: "<?=security::htmlsafe($url)?>",
  width: 200,
	height: 200
});
</dynscript>

<form action="doenablesecondfactor.php" method="POST" autocomplete="off">
  <input type="hidden" name="secret" value="<?=security::htmlsafe($secret)?>">
  <h4 class="mdl-dialog__title">Activa la verificación en dos pasos</h4>
  <div class="mdl-dialog__content">
    <p>Para activar la verificación en dos pasos, sigue los siguientes pasos:</p>

    <div class="step">
      <div class="number">1</div>
      <div class="text"><b>Instala la aplicación Google Authenticator en tu <a href="http://appstore.com/googleauthenticator" target="_blank" rel="noopener noreferrer">iPhone</a> o <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" rel="noopener noreferrer">Android</a>.</b><br>También puedes usar otra aplicación si lo prefieres.</div>
    </div>
    <div class="step">
      <div class="number">2</div><div class="text"><b>Configura tu cuenta en la app Google Authenticator escaneando el siguiente código QR:</b></div>
    </div>

    <div id="qrcode"></div>

    <div class="step" style="border-top: 1px solid #ebebeb;">
        <div class="number">3</div><div class="text"><b>¿No puedes escanear el código QR? Introduce manualmente la siguiente clave secreta:</b><br><?=security::htmlsafe(secondFactorView::renderSecret($secret))?></div>
    </div>
    <div class="step" style="margin-bottom: 5px;">
        <div class="number">4</div><div class="text"><b>Introduce el código de verificación de 6 dígitos:</b></div>
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
      <input class="mdl-textfield__input" type="text" name="code" id="code" autocomplete="off" pattern="[0-9]{6}" data-required>
      <label class="mdl-textfield__label" for="code">Código de verificación</label>
    </div>
  </div>
  <div class="mdl-dialog__actions">
    <button type="submit" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary">Activar</button>
    <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cancelar</button>
  </div>
</form>
