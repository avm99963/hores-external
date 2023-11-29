<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$id = (int)$_GET["id"];

$c = calendars::get($id);

if ($c === false) {
  security::notFound();
}

$details = json_decode($c["details"], true);
$export = array(
  "begins" => $c["begins"],
  "ends" => $c["ends"],
  "calendar" => $details
);
?>

<style>
textarea.code {
  width: 100%;
  height: 100px;
}
</style>

<dynscript>
document.querySelector("textarea.code").select();

document.getElementById("copy").addEventListener("click", _ => {
  navigator.clipboard.writeText(document.querySelector("textarea.code").value).then(_ => {
    document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar({
      message: "Se ha copiado el texto correctamente.",
      timeout: 5000
    });
  }).catch(error => {
    document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar({
      message: "Ha ocurrido un error copiando el texto. Por favor, cópialo manualmente.",
      timeout: 5000
    });
    console.error(error);
  });
});
</dynscript>

<h4 class="mdl-dialog__title">Exportar calendario</h4>
<div class="mdl-dialog__content">
  <p>Este es el código que contiene toda la información del calendario y que puedes usar de plantilla más tarde:</p>
  <textarea class="code" readonly><?=security::htmlsafe(json_encode($export))?></textarea>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--accent cancel">Cerrar</button>
  <button id="copy" class="mdl-button mdl-js-button mdl-js-ripple-effect">Copiar</button>
</div>
