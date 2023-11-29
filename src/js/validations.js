function getFormData() {
  var data = {
    "incidents": [],
    "records": []
  };

  ["incident", "record"].forEach(key => {
    document.querySelectorAll("input[type=\"checkbox\"][data-"+key+"]:checked").forEach(el => {
      data[key+"s"].push(el.getAttribute("data-"+key));
    });
  });

  return data;
}

window.addEventListener("load", function() {
  document.querySelectorAll(".mdl-checkbox[data-check-all=\"true\"] input[type=\"checkbox\"]").forEach(el => {
    el.addEventListener("change", e => {
      el.parentElement.parentElement.parentElement.parentElement.parentElement.querySelectorAll(".mdl-checkbox:not([data-check-all=\"true\"])").forEach(input => {
        var checkbox = input.MaterialCheckbox;

        if (el.checked) checkbox.check();
        else checkbox.uncheck();
      });
    });
  });

  document.querySelector("#submit").addEventListener("click", e => {
    var data = getFormData();

    if (data.incidents.length == 0 && data.records.length == 0) {
      document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar({
        message: "Debes seleccionar al menos una incidencia o registro para poder validar.",
        timeout: 5000
      });

      return;
    }

    var form = document.createElement("form");
    form.setAttribute("action", "interstitialvalidations.php");
    form.setAttribute("method", "POST");
    form.style.display = "none";

    ["incidents", "records"].forEach(key => {
      var input = document.createElement("input");
      input.setAttribute("name", key);
      input.setAttribute("value", data[key]);
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  });
});
