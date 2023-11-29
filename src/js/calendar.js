document.addEventListener("DOMContentLoaded", _ => {
  document.querySelectorAll("select").forEach(el => {
    el.addEventListener("change", _ => {
      el.setAttribute("data-value", el.value);
    });
    el.setAttribute("data-value", el.value);
  });
});
