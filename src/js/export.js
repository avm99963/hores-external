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
