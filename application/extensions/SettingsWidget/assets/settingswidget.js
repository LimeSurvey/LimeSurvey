$(document).ready(function() {
    var removeRow = function ()
    {
        // Don't remove last row.
        if ($(this).closest('tbody').children().length > 1)
        {
            $(this).closest('tr').fadeOut(400, function() { $(this).remove(); });
        }

    };

    var addRow = function()
    {
        var baseRow = $(this).closest('tr');
        // Create row, hidden and with empty inputs.
        var newRow = baseRow.clone(true);
        newRow.find('input').each(function() { $(this).val(''); });

        // Check if the first element contains a number value and, if so, increase it by one.
        var parts = baseRow.find('input:first').val().match(/([\d]+|[^\d]+)/g);
        if (parts == null)
        {
            parts = [0];
        }
        for (var i = parts.length - 1; i >= 0; --i)
        {
            var num = parseInt(parts[i]);
            var length = parts[i].length;
            if (num === num)
            {
                parts[i] = (num + 1).toString();
                while (parts[i].length < length)
                {
                    parts[i] = '0' + parts[i];
                }
            }
        }
        newRow.find('input:first').val(parts.join(''));
        baseRow.after(newRow);
        newRow.fadeIn();

    }
    $('.settingswidget .setting-list a.remove').bind('click',removeRow);
    $('.settingswidget .setting-list a.add').bind('click', addRow);

})

/* select2 ctrl+a/ctrl+d select all, deselect all */
$(document).on("keypress",".select2-container-multi .select2-input",function(event){
    if (event.ctrlKey || event.metaKey) {
        var id =$(this).parents("div[class*='select2-container']").attr("id").replace("s2id_","");
        var element =$("#"+id);

        if (event.which == 97){
            var selected = [];
            $('.select2-drop-active').find("ul.select2-results li").each(function(i,e){
                selected.push($(e).data("select2-data"));
            });
            element.select2("data", selected);
            element.select2("focus");
            event.preventDefault();
        } else if (event.which == 100){
            element.select2("data", []);
            event.preventDefault();
        }
    }
});
