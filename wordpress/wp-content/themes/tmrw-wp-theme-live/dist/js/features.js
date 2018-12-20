"use strict";

//Features JS
console.log('features');
$(document).ready(function () {
  $('input[type="radio"]').click(function () {
    var inputValue = $(this).attr("value").toLowerCase();
    console.log(inputValue);
    $.ajax({
      type: 'post',
      url: ajaxurl,
      data: {
        'action': 'get_cat',
        'cat': inputValue
      },
      success: function success(data) {
        $('.features-list').html(data);
      }
    });
  });
  $(document).ajaxStart(function () {
    console.log('start');
  }).ajaxStop(function () {
    console.log('end');
  });
});
$('.features-list').on('click', '#load-more-button', function (e) {
  console.log('being clicked'); // prevent new page load

  e.preventDefault(); // store next page number

  var next_page = $(this).attr("data-href"); // remove older posts button from DOM

  console.log(next_page);
  $('.feature-wrapper-load').append($('.feature-wrapper-load').load(next_page + ' .feature-wrapper-load'));
});