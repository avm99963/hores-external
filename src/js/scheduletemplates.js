window.addEventListener("load", function() {
  document.querySelector(".addtemplate").addEventListener("click", function() {
    document.querySelector("#addtemplate").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
