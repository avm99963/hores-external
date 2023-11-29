window.addEventListener("load", function() {
  document.querySelectorAll("tr[data-worker-id]").forEach(tr => {
    var checkbox = tr.querySelector("input[type=\"checkbox\"]");

    checkbox.setAttribute("name", "workers[]");
    checkbox.setAttribute("value", tr.getAttribute("data-worker-id"));
  });
});
