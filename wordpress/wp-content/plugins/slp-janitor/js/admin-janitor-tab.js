var SLP_ADMIN = SLP_ADMIN || {};
SLP_ADMIN.janitor = SLP_ADMIN.janitor|| {};

/**
 * Process Janitor form elements
 *
 * @param e
 */
SLP_ADMIN.janitor.do = function(e) {
    e.preventDefault();
    var data = jQuery(e.target).data();
    if (confirm(data.related_to)) {
        var this_form = jQuery( '#slp_janitor' );
        jQuery(this_form).find('input[name="action"] ').val(data.field);
        jQuery(this_form).submit();
    }
};

/*
 * When the document has been loaded...
 *
 */
jQuery(document).ready( function() {
    jQuery('input:submit').on('click', SLP_ADMIN.janitor.do );
    jQuery('a.dashicons-trash').on('click', SLP_ADMIN.janitor.do );
});