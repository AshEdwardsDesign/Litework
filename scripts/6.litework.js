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

// Isotope
$(document).ready(function() {
  // init Isotope
  var $grid = $('.grid').isotope({
    itemSelector: '.grid-item',
  });

  // store filter for each group
  var filters = {};

  $('.filters').on('click', '.button', function() {
    var $this = $(this);
    // get group key
    var $buttonGroup = $this.parents('.button-group');
    var filterGroup = $buttonGroup.attr('data-filter-group');
    // set filter for group
    filters[filterGroup] = $this.attr('data-filter');
    // combine filters
    var filterValue = concatValues(filters);
    // set filter for Isotope
    $grid.isotope({
      filter: filterValue
    });
  });

  //  change is-checked class on buttons
  $('.button-group').each(function(i, buttonGroup) {
    var $buttonGroup = $(buttonGroup);
    $buttonGroup.on('click', 'button', function() {
      $buttonGroup.find('.is-checked').removeClass('is-checked');
      $(this).addClass('is-checked');
    });
  });

});

// flatten object by concatting values
function concatValues(obj) {
  var value = '';
  for (var prop in obj) {
    value += obj[prop];
  }
  return value;
}
