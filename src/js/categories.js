window.addEventListener("load", function() {
  document.querySelector(".addcategory").addEventListener("click", function() {
    document.querySelector("#addcategory").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
