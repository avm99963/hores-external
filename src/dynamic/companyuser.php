<?php
require_once(__DIR__."/../core.php");
security::checkType(security::ADMIN, security::METHOD_NOTFOUND);

if (!isset($_GET["id"])) {
  security::notFound();
}

$p = people::get($_GET["id"]);

if ($p === false) {
  security::notFound();
}

$companies = companies::getAll();
?>

<style>
.mdl-dialog__content {
  color: rgba(0,0,0,.87)!important;
}

#dynDialog {
  max-width: 300px;
  width: auto;
}
</style>

<dynscript>
var person = <?=(int)$p["id"]?>;

document.querySelectorAll("button[data-company-id]").forEach(btn => {
  btn.addEventListener("click", e => {
    var id = e.currentTarget.getAttribute("data-company-id");
    fetch("ajax/addpersontocompany.php", {
      method: "post",
      body: "person="+parseInt(person)+"&company="+parseInt(id),
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      }
    }).then(response => {
      response.text().then(text => console.log);
      dynDialog.reload();
    }).catch(error => {
      alert("Ha habido un error dando de alta a este trabajador de esta empresa: "+error);
    });
  });
});

document.querySelectorAll("button[data-worker-id]").forEach(btn => {
  btn.addEventListener("click", e => {
    var id = e.currentTarget.getAttribute("data-worker-id");
    dynDialog.load("dynamic/workhistory.php?id="+parseInt(id));
  });
});
</dynscript>

<h4 class="mdl-dialog__title"><?=security::htmlsafe($p["name"])?></h4>
<div class="mdl-dialog__content">
<?php
$list = [];
$list["visible"] = "";
$list["hidden"] = "";

$workers = workers::getPersonWorkers($p["id"]);
foreach ($workers as $w) {
  $list[($w["hidden"] ? "hidden" : "visible")] .= '<li>'.
    security::htmlsafe($companies[$w["company"]]).'
    <button class="mdl-button mdl-js-button mdl-button--icon" title="Acceder al historial de altas y bajas" data-worker-id="'.(int)$w["id"].'">
      <i class="material-icons">history</i>
    </button>
    <br>
    <span class="mdl-color-text--grey-600">'.($w["hidden"] ? "Dada de baja" : "Dada de alta").' el '.date("d/m/Y", $w["lastupdated"]).'</span></li>';
}
?>
  <p><b>Dada de alta en:</b></p>
  <?php
  if (!empty($list["visible"])) {
    echo "<ul>".$list["visible"]."</ul>";
  }
  ?>
  <p><b>Dada de baja en:</b></p>
  <?php
  if (!empty($list["hidden"])) {
    echo "<ul>".$list["hidden"]."</ul>";
  }
  ?>
  <p><b>No dada de alta en:</b></p>
  <ul>
    <?php
    foreach ($companies as $id => $name) {
      if (in_array($id, $p["companies"])) continue;
      ?>
      <li><?=security::htmlsafe($name)?> <button class="mdl-button mdl-js-button mdl-button--icon mdl-color-text--green" title="Dar de alta en esta empresa" data-company-id="<?=(int)$id?>"><i class="material-icons">add</i></button></li>
      <?php
    }
    ?>
  </ul>
</div>
<div class="mdl-dialog__actions">
  <button data-dyndialog-close class="mdl-button mdl-js-button mdl-js-ripple-effect cancel">Cerrar</button>
</div>
