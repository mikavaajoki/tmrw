var cslmap
    slp = slp || {};

/**
 * Send and AJAX request and process the response.
 *
 * @param action
 * @param callback
 */
slp.send_ajax = function (action, callback) {
    jQuery.post(
        slplus.ajaxurl,
        action,
        function (response) {
            try {
                response = JSON.parse(response);
            }
            catch (ex) {
            }
            callback(response);
        }
    );
};

jQuery(document).ready( slp.run );
