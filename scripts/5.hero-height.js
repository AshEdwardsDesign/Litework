$(document).ready(function() {
  function setHeight() {
    windowHeight = $(window).innerHeight();
    $('#hero').css('min-height', windowHeight);
  };
  setHeight();

  $(window).resize(function() {
    setHeight();
  });
});
