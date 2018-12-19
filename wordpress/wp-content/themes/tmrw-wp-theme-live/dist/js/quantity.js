"use strict";

/**
* Quantity Buttons
*	Version: 2.0.0
*	Description: Adds quantity buttons for Wordpress
*	Author: Greatives/D.A
*/
!function (t) {
  t(document).ready(function () {
    function e() {
      t(".quantity input[type=number]").each(function () {
        var e = t(this),
            n = parseFloat(e.attr("max")),
            a = parseFloat(e.attr("min")),
            r = parseInt(e.attr("step"), 10),
            u = t(t("<div />").append(e.clone(!0)).html().replace("number", "text")).insertAfter(e);
        e.remove(), setTimeout(function () {
          if (0 == u.next(".plus").length) {
            var e = t('<input type="button" value="-" class="quantity-button minus">').insertBefore(u),
                i = t('<input type="button" value="+" class="quantity-button plus">').insertAfter(u);
            e.on("click", function () {
              var t = parseInt(u.val(), 10) - r;
              t = 0 > t ? 0 : t, t = a > t ? a : t, u.val(t).trigger("change");
            }), i.on("click", function () {
              var t = parseInt(u.val(), 10) + r;
              t = t > n ? n : t, u.val(t).trigger("change");
            });
          }
        }, 10);
      });
    }

    e(), t(document).on("updated_cart_totals", e);
  });
}(jQuery);