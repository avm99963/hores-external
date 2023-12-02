/* (license-header)
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */
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
