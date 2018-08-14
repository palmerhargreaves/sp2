/**
 * Created by kostet on 15.08.2017.
 */

$(function() {
    var self = this;

    self.SERVER_ACTION_ON_ORDER = '/backend.php/main_menu_items/reorder';
    self.initTableDnD = function() {
        $('#menus-list').tableDnD({
            hierarchyLevel: 0,
            onDragStart: function(table, row) {
                $(table).parent().find('.result').text('');
            },
            onDrop: function(table, row) {
                $.post(self.SERVER_ACTION_ON_ORDER,
                    { data : $.tableDnD.jsonize() },
                    function(result) {
                    });
            }
        });
    }

    self.initTableDnD();
});
