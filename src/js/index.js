window.addEventListener("load", function() {
  if (document.querySelector("#recoverybtn")) {
    document.querySelector("#recoverybtn").addEventListener("click", function(e) {
      e.preventDefault();
      document.querySelector("#recovery").showModal();
    });
  }
});
