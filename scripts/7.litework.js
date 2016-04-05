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

    parentSelector: '#homepage-hero'
  });
});
