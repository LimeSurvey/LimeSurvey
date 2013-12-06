(function($){

    /**
     * Returns the key values of the currently checked rows.
     * @param id string the ID of the grid view container
     * @param column_id string the ID of the column
     * @return array the key values of the currently checked rows.
     */
    $.fn.yiiGridView.selectable = function (id, filter, callback)
    {
        var grid = $('#'+id) ;
        var selectedItems = [];

        $("tbody", grid).selectable({
            filter: filter,
            distance: 1,
            start: function(e, ui) {
                selectedItems = [];
            },
            selected: function(e, ui){
                selectedItems.push(ui.selected);
            },
            stop: function(e,ui){
                if($.isFunction(callback))
                {
                    callback(selectedItems);
                }
            }
        }).disableSelection();

        grid.on('click', function(){
            $(".ui-selected", grid).removeClass("ui-selected");
        });
    };


})(jQuery);