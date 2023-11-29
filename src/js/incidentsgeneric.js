window.addEventListener("load", function() {

  document.querySelectorAll(".custom-actions-btn").forEach(el => {
    el.addEventListener("click", e => {
      var forId = el.getAttribute("id");
      var menu = document.querySelector("[for=\""+forId+"\"]").parentElement;
      var overflow = menu.parentElement;

      menu.style.left = el.offsetLeft - menu.offsetWidth + el.offsetWidth - overflow.scrollLeft + 'px';
      menu.style.top = el.offsetTop + el.offsetHeight - overflow.scrollTop  + 'px';
    });
  });
});
