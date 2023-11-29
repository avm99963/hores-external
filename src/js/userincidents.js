window.addEventListener("load", function() {
  document.querySelector(".addincident").addEventListener("click", function() {
    document.querySelector("#addincident").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
