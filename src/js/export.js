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
function toggleTr(tr, show) {
  var checkbox = tr.querySelector("label").MaterialCheckbox;
  if (show) {
    tr.style.display = "table-row";
    checkbox.enable();
  } else {
    tr.style.display = "none";
    checkbox.disable();
  }
}

window.addEventListener("load", function() {
  document.querySelectorAll("tr[data-worker-id]").forEach(tr => {
    var checkbox = tr.querySelector("input[type=\"checkbox\"]");

    checkbox.setAttribute("name", "workers[]");
    checkbox.setAttribute("value", tr.getAttribute("data-worker-id"));

    toggleTr(tr, false);
  });

  document.querySelectorAll(".select-all").forEach(el => {
    el.addEventListener("click", e => {
      var allchecked = true;
      el.getAttribute("data-workers").split(",").forEach(workerid => {
        var tr = document.querySelector("tr[data-worker-id=\""+workerid+"\"]");
        var checkbox = tr.querySelector("label").MaterialCheckbox;
        if (checkbox.inputElement_.disabled) return;
        if (!checkbox.inputElement_.checked) allchecked = false;
        tr.classList.add("is-selected");
        checkbox.check();
      });

      if (allchecked) {
        el.getAttribute("data-workers").split(",").forEach(workerid => {
          var tr = document.querySelector("tr[data-worker-id=\""+workerid+"\"]");
          var checkbox = tr.querySelector("label").MaterialCheckbox;
          tr.classList.remove("is-selected");
          checkbox.uncheck();
        });
      }
    });
  });

  var multiselectEl = document.querySelector(".mdl-custom-multiselect");
  if (multiselectEl !== null) {
    multiselectEl.addEventListener("custom-multiselect-change", e => {
      var companies = [];
      document.querySelectorAll(".mdl-custom-multiselect .mdl-custom-multiselect__item input[type=\"checkbox\"]").forEach(checkbox => {
        if (checkbox.checked) {
          companies.push(checkbox.value);
        }
      });

      document.querySelectorAll("tr[data-worker-id]").forEach(tr => {
        toggleTr(tr, companies.includes(tr.getAttribute("data-company-id")));
      });
    });
  }

  document.querySelectorAll("input[name=\"companies\[\]\"]").forEach(input => {
    input.checked = true;
    var customevent = document.createEvent("HTMLEvents");
    customevent.initEvent("change", false, true);
    input.dispatchEvent(customevent);
  });

  document.getElementById("format").addEventListener("change", e => {
    document.getElementById("pdf").style.display = (document.getElementById("format").value != "1" && document.getElementById("format").value != "2" ? "none" : "block");
  });
});
