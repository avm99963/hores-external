window.addEventListener("load", function() {
  document.querySelector(".addschedule").addEventListener("click", function() {
    document.querySelector("#addschedule").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
