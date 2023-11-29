window.addEventListener("load", function() {
  document.querySelector(".addday").addEventListener("click", function() {
    document.querySelector("#addday").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
