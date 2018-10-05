/**
 * Created by kostet
 */
MainMenu = function(config) {

    $.extend(this, config);
}

MainMenu.prototype = {
    start: function() {
        this.initEvents();

        return this;
    },

    initEvents: function() {
        this.checkMenuItem();
    },

    checkMenuItem: function() {
        var $t_link = undefined, link_exists = false;

        this.getMenu().find('a').each(function(ind, el) {
            var $a_item = $(el), a_link = $a_item.attr('href').split('/'), a_link_path = a_link.pop();

            if ($a_item.data('not-using') == undefined) {

                if (a_link_path.length == 0) {
                    $t_link = $a_item;
                }
                else if (RegExp(a_link_path, 'gi').test(window.location.href.split('/').pop())) {
                    $a_item.addClass('active');
                    link_exists = true;
                }
            }
        });

        if (!link_exists && $t_link != undefined) {
            $t_link.addClass('active');
        }
    },

    getMenu: function() {
        return $('.nav-main');
    }
}
