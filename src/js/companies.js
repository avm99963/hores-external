window.addEventListener("load", function() {
  document.querySelector(".addcompany").addEventListener("click", function() {
    document.querySelector("#addcompany").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
