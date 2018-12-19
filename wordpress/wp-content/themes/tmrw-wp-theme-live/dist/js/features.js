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
    $("#loading").show();
  }).ajaxStop(function () {
    $("#loading").hide();
  });
});