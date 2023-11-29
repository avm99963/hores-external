window.addEventListener("load", _ => {
  document.getElementById("showinvalidated").addEventListener("change", e => {
    document.getElementById("show-invalidated-form").submit();
  });
});
