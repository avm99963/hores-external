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
  var incidents = [];

  document.querySelectorAll("input[type=\"checkbox\"][data-incident]:checked").forEach(el => {
    incidents.push(el.getAttribute("data-incident"));
  });

  return incidents;
}

function getParameters() {
  var parameters = [];
  var incidents = getFormData();
  incidents.forEach(incident => {
    parameters.push("incidents[]="+incident);
  });

  if (parameters.length == 0) return false;

  return parameters.join("&");
}

window.addEventListener("load", function() {
  document.querySelector(".addincident").addEventListener("click", function() {
    document.querySelector("#addincident").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  document.querySelector(".addrecurringincident").addEventListener("click", function() {
    document.querySelector("#addrecurringincident").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  document.querySelector(".filter").addEventListener("click", function() {
    document.querySelector("#filter").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  if (_showResultsPaginated) {
    document.getElementById("limit-change").addEventListener("change", _ => {
      var limit = parseInt(document.getElementById("limit-change").value);
      var firstIncidentPos = _page*_limit;
      var page = Math.floor(firstIncidentPos/limit) + 1;

      var url = new URL(location.href);
      url.searchParams.set("limit", limit);
      url.searchParams.set("page", page);
      location.href = url;
    });
  }

  document.querySelectorAll(".mdl-checkbox[data-check-all=\"true\"] input[type=\"checkbox\"]").forEach(el => {
    el.addEventListener("change", e => {
      el.parentElement.parentElement.parentElement.parentElement.parentElement.querySelectorAll(".mdl-checkbox:not([data-check-all=\"true\"])").forEach(input => {
        var checkbox = input.MaterialCheckbox;
        if (checkbox.inputElement_.disabled) return;

        if (el.checked) checkbox.check();
        else checkbox.uncheck();
      });
    });
  });

  document.getElementById("deleteincidentsbulk").addEventListener("click", e => {
    var parameters = getParameters();

    if (parameters === false) {
      document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar({
        message: "Debes seleccionar al menos una incidencia para poder eliminar.",
        timeout: 5000
      });

      return;
    }

    var url = "dynamic/deleteincidentsbulk.php?"+parameters;
    dynDialog.load(url);
  });
});
