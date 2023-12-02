/* (license-header)
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
