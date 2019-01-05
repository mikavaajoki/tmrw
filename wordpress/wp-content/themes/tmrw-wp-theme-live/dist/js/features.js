"use strict";

//Features JS
console.log('features');
var inputValue;
var paged = 2;
$(document).ready(function () {
  $('input[type="radio"]').click(function () {
    inputValue = $(this).attr("value").toLowerCase();
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
    $('.features-list').addClass("features-loading");
  }).ajaxStop(function () {
    $('.features-list').removeClass("features-loading");
  });
});
$('.load-more').on('click', '#load-more-button', function (e) {
  e.preventDefault();
  paged++;
  console.log(paged); //Update button URL

  var buttonURL = "http://tmrw-mag.test/features/page/".concat(paged);
  console.log(buttonURL);
  $(this).attr('data-href', buttonURL);
  $.ajax({
    type: 'post',
    url: ajaxurl,
    data: {
      'action': 'get_cat',
      'cat': inputValue,
      'paged': paged
    },
    success: function success(data) {
      $(data).appendTo(".features-list").hide().fadeIn(500);
    }
  }); // store next page number
  // $('.features-list').append( $('.feature-wrapper-load').load(next_page + ' .feature-wrapper-load') );
});