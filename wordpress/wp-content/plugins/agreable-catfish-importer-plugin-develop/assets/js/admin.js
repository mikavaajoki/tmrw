(function ($) {
    $(function () {
        var $reImportButtons = $('.js-catfish-reimport');
        $reImportButtons.click(function (e) {
            e.preventDefault();
            var $this = $(this);
            var data = {
                'action': 'catfish_reimport',
                'id': $this.data('id')
            };
            $.post(ajaxurl, data).done(function () {
                alert('Post was updated successfully');
            }).fail(function () {
                alert('Post failed to update. Contact nearest developer');
            });
        });
    });
})(jQuery);
