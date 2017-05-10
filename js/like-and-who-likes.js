jQuery(document).ready(function ($) {
    $(document).on('click', '.wl-like', function () {
        var button = $(this);
        var type = button.hasClass('wl-unlike') ? 'unlike' : 'like';
        var component = button.data('component');
        var id = button.data('id');
        var list = $('.wl-list[data-component=' + component + '][data-id=' + id + ']');

        if (button.hasClass('loading')) {
            return false;
        }

        button.addClass('loading');

        $.post(who_likes.ajax_url, {action: 'like_and_who_likes', type: type, component: component, id: id}, function (data) {
            if (data) {
                button.replaceWith(data.button);
                list.replaceWith(data.list);
            }

        }, 'json');

        return false;
    });
});