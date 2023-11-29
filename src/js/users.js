window.addEventListener("load", function() {
  var datatable = $('.datatable').DataTable({
    paging:   false,
    ordering: false,
    info:     false,
    searching:true
  });

  document.querySelector("#usuario").addEventListener("input", function(evt) {
    this.search(evt.target.value);
    this.draw(true);
  }.bind(datatable));

  document.querySelector(".adduser").addEventListener("click", function() {
    document.querySelector("#adduser").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  document.querySelector(".importcsv").addEventListener("click", function() {
    document.querySelector("#importcsv").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  document.querySelector(".filter").addEventListener("click", function() {
    document.querySelector("#filter").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });
});
