// Call SlickNav

$(function() {
  $('#menu').slicknav();
});

// ScrollReveal
(function($) {

  'use strict';

  window.sr = new scrollReveal({
    reset: false,
    mobile: false
  });

})();

// Hero Centering
$(document).ready(function() {
  $('#hero-centered').flexVerticalCenter({

    parentSelector: '#hero'
  });
});

// Nav centering
$(document).ready(function() {
  $('#menu').flexVerticalCenter({

    parentSelector: '#navstrip'
  });
});
