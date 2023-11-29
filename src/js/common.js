/**
  * Functions hasClass(), addClass() and removeClass() developed by Jake Trent (http://jaketrent.com/post/addremove-classes-raw-javascript/)
  */
function hasClass(el, className) {
  if (el.classList)
    return el.classList.contains(className)
  else
    return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'))
}

function addClass(el, className) {
  if (el.classList)
    el.classList.add(className)
  else if (!hasClass(el, className)) el.className += " " + className
}

function removeClass(el, className) {
  if (el.classList)
    el.classList.remove(className)
  else if (hasClass(el, className)) {
    var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
    el.className=el.className.replace(reg, ' ')
  }
}

// MultiSelect implementation:
var MultiSelect = function MultiSelect(element) {
  this.element_ = element;
  this.selected_ = [];

  var forElId = this.element_.getAttribute("for") || this.element_.getAttribute("data-for");
  if (forElId) {
    this.forEl_ = (forElId ? document.getElementById(this.element_.getAttribute("for")) : null);
  }

  this.init();
};

MultiSelect.prototype.renderSummary = function() {
  if (this.forEl_) {
    this.forEl_.innerText = this.selected_.join(", ") || "-";
  }
};

MultiSelect.prototype.init = function() {
  if (this.element_) {
    this.element_.addEventListener("click", e => {
      e.stopImmediatePropagation();
    }, true);

    this.element_.querySelectorAll(".mdl-custom-multiselect__item").forEach(item => {
      var checkbox = item.querySelector("input[type=\"checkbox\"]");
      var label = item.querySelector(".mdl-checkbox__label").innerText;
      checkbox.addEventListener("change", e => {
        if(checkbox.checked) {
          this.selected_.push(label);
        } else {
          this.selected_ = this.selected_.filter(item => item !== label);
        }
        this.renderSummary();

        var customEvent = new Event('custom-multiselect-change');
        this.element_.dispatchEvent(customEvent);
      });
    });
  }
};

var dynDialog = {
  didItInit: false,
  dialog: null,
  url: null,
  load: function(url, reload) {
    if (this.didItInit === false) {
      this.init();
    }

    if (this.url == url && reload !== true) {
      this.show();
      return;
    }

    this.url = url;

    fetch(url).then(response => response.text()).then(response => {
      if (this.dialog.open) {
        this.close();
      }

      this.dialog.innerHTML = response;
      componentHandler.upgradeElements(this.dialog);

      this.dialog.querySelectorAll("[data-required]").forEach(input => {
        input.setAttribute("required", "true");
      });

      var script = this.dialog.querySelectorAll("dynscript");
      if (script.length > 0) {
        for (var i = 0; i < script.length; i++) {
          eval(script[i].innerText);
        }
      }
      this.dialog.querySelectorAll("[data-dyndialog-close]").forEach(btn => {
        btn.addEventListener("click", e => {
          e.preventDefault();
          this.close();
        });
      });

      this.dialog.showModal();
    });
  },
  reload: function() {
    this.load(this.url, true);
  },
  show: function() {
    this.dialog.showModal();
  },
  close: function() {
    this.dialog.close();
  },
  init: function() {
    this.dialog = document.createElement("dialog");
    this.dialog.setAttribute("id", "dynDialog");
    this.dialog.setAttribute("class", "mdl-dialog");
    dialogPolyfill.registerDialog(this.dialog);
    document.body.appendChild(this.dialog);

    this.didItInit = true;
  }
};

// From nodep-date-input-polyfill
function isDateInputSupported() {
  const input = document.createElement("input");
  input.setAttribute("type", "date");

  const notADateValue = "not-a-date";
  input.setAttribute("value", notADateValue);

  return (input.value !== notADateValue);
}

document.addEventListener("DOMContentLoaded", function() {
  var dialogs = document.querySelectorAll("dialog");
  for (var i = 0; i < dialogs.length; i++) {
    dialogPolyfill.registerDialog(dialogs[i]);
  }

  document.querySelectorAll("[data-dyndialog-href]").forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();
      dynDialog.load(link.getAttribute("data-dyndialog-href"));
    });
  });

  document.querySelectorAll(".mdl-custom-multiselect-js").forEach(menu => {
    new MultiSelect(menu);
  });
});

function importCSS(url) {
  var link = document.createElement("link");
  link.setAttribute("rel", "stylesheet");
  link.setAttribute("href", url);
  document.head.appendChild(link);
}

var loadScriptAsync = function(uri) {
  return new Promise((resolve, reject) => {
    var script = document.createElement('script');
    script.src = uri;
    script.async = true;
    script.onload = () => {
      resolve();
    };
    document.head.appendChild(script);
  });
}

function hasParentDialog(el) {
  while (el != document.documentElement) {
    if (el.tagName == "DIALOG") {
      return el;
    }

    el = el.parentNode;
  }

  return undefined;
}

function polyfillDateSupport(container) {
  container.querySelectorAll("input[type=\"date\"]").forEach(el => {
    el.setAttribute("type", "text");

    dialogParent = hasParentDialog(el);

    var options = {
      format: "yyyy-mm-dd",
      todayHighlight: true,
      weekStart: 1,
      zIndexOffset: 100,
      container: dialogParent || container
    };

    if (el.hasAttribute("max")) {
      options.endDate = el.getAttribute("max");
    }

    $(el).datepicker(options);
  });

  container.querySelectorAll("input[type=\"time\"]").forEach(el => {
    el.setAttribute("placeholder", "hh:mm");
  });
}

function initPolyfillDateSupport() {
  console.info("Polyfilling date support");

  importCSS("https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.standalone.min.css", "css");

  loadScriptAsync("https://unpkg.com/jquery@3.4.1/dist/jquery.min.js", "js").then(_ => {
    return loadScriptAsync("https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js");
  }).then(_ => {
    return loadScriptAsync("https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.es.min.js");
  }).then(_ => {
    console.log("[Date polyfill] Scripts loaded.");
    polyfillDateSupport(document.documentElement);
  });
}

window.addEventListener("load", function() {
  document.querySelectorAll("[data-required]").forEach(input => {
    input.setAttribute("required", "true");
  });

  if (!isDateInputSupported()) {
    initPolyfillDateSupport();
  }
});
