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

// // Call Isotope
// $('.grid').isotope({
//
//   // bind category button click
//   $('.category-buttons').on('click', 'button', function() {
//     var filterValue = $(this).attr('data-filter');
//     // use filterFn if matches value
//     filterValue = filterFns[filterValue] || filterValue;
//     $grid.isotope({
//       filter: filterValue
//     });
//   });
//
//   /*
//
//           // bind price button click
//           $('.price-buttons').on('click', 'button', function() {
//             var filterValue = $(this).attr('data-filter');
//             // use filterFn if matches value
//             filterValue = filterFns[filterValue] || filterValue;
//             $grid.isotope({
//               filter: filterValue
//             });
//           });
//
//   */
//
//   // change is-checked class on buttons
//   $('.button-group').each(function(i, buttonGroup) {
//     var $buttonGroup = $(buttonGroup);
//     $buttonGroup.on('click', 'button', function() {
//       $buttonGroup.find('.active').removeClass('active');
//       $(this).addClass('active');
//     });
//   });
//
//   // options
//   itemSelector: '.grid-item',
//     layoutMode: 'fitRows',
//     percentPosition: true,
//     masonry: {
//       // use outer width of grid-sizer for columnWidth
//       columnWidth: '.grid-sizer',
//       gutter: 10
//     }


});
