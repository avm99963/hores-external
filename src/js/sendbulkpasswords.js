window.addEventListener("load", function() {
  document.querySelectorAll("tr[data-person-id]").forEach(tr => {
    var checkbox = tr.querySelector("input[type=\"checkbox\"]");

    checkbox.setAttribute("name", "people[]");
    checkbox.setAttribute("value", tr.getAttribute("data-person-id"));
  });
});
