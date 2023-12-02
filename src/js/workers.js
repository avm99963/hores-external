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
function getRawWorkers() {
  var parameters = [];
  document.querySelectorAll("tr[data-worker-id]").forEach(tr => {
    if (tr.querySelector("input[type=\"checkbox\"]").checked) {
      parameters.push(tr.getAttribute("data-worker-id"));
    }
  });

  return parameters;
}

function getParameters() {
  var parameters = [];
  var workers = getRawWorkers();
  workers.forEach(worker => {
    parameters.push("workers[]="+worker);
  });

  if (parameters.length == 0) return false;

  return parameters.join("&");
}

window.addEventListener("load", function() {
  var datatable = $('.datatable').DataTable({
    paging:   false,
    ordering: false,
    info:     false,
    searching:true
  });

  document.querySelector("#usuario").addEventListener("input", function(evt) {
    this.search(evt.target.value);
    this.draw(true);
  }.bind(datatable));

  document.querySelector(".filter").addEventListener("click", function() {
    document.querySelector("#filter").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  ["copytemplate", "addincidentbulk"].forEach(action => {
    document.getElementById(action).addEventListener("click", function() {
      var parameters = getParameters();
      if (parameters === false) return;

      var url = "dynamic/"+action+".php?"+parameters;
      dynDialog.load(url);
    });
  });

  document.getElementById("addrecurringincident").addEventListener("click", function () {
    var workers = getRawWorkers();
    if (workers.length > 1) {
      if (document.querySelector(".mdl-js-snackbar") === null) {
        document.body.insertAdjacentHTML('beforeend', '<div class="mdl-snackbar mdl-js-snackbar"><div class="mdl-snackbar__text"></div><button type="button" class="mdl-snackbar__action"></button></div>');
        componentHandler.upgradeElement(document.querySelector(".mdl-js-snackbar"));
      }

      document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar(
        {
          message: "Solo se puede añadir una incidencia recurrente a un solo trabajador.",
          timeout: 5000
        }
      );
      // Display error message
    } else if (workers.length == 1) {
      window.location = "incidents.php?openRecurringFormWorker="+workers[0];
    }
  });
});
