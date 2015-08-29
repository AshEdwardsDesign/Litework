// Call SlickNav

$(function() {
  $('#menu').slicknav();
});

// ScrollReveal
(function($) {

  'use strict';

  window.sr = new scrollReveal({
    reset: true,
    mobile: false
  });

})();

// Hero Centering
$(document).ready(function() {
  $('#hero-centered').flexVerticalCenter({

    parentSelector: '#hero'
  });
});
