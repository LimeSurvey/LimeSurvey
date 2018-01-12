
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready pjax:scriptcomplete', function() {
    $.each(removeitem, function(index, value) {
        $("select#langdata option[value='"+value+"']").remove();
    });
    /* Only show the dropdown values if DD is chosen as attribute type */
    if($('#attribute_type').val()=="DD") {
        $('#ddtable').css('display','');
    }
    $('#attribute_type').change(function(){
        if($('#attribute_type').val()=="DD") {
            $('#ddtable').css('display','');
        } else {
            $('#ddtable').css('display','none');
        }
    });

    $("#tabs").tabs({
        add: function(event, ui) {
            $("#tabs").tabs('select', '#' + ui.panel.id);
           //$("#"+ui.panel.id).append("<center><label for='attname'>"+attname+"</label>"+attnamebox);
        },
        show: function(event, ui) {
                //load function to close selected tabs
        }
    });

    $("#add").click(function(){
        var lang = $("#langdata").val();
        if(lang != "")
        {
            var num_tabs = $('#tabs ul li').length;

            // Add li anchor and content
            $('#tabs ul').append(
                '<li data-toggle="tab" class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="' + lang + '" aria-labelledby="ui-id-' + num_tabs + '"aria-selected="fase"><a class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-' + num_tabs + '"href="#' + lang + '">' + $('#langdata option:selected').text() + '</a></li>'
            );
            $('#tabs').append('<div class="commonsettings"><div id="' + lang + '" aria-labelledby="ui-id-' + num_tabs + '" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="true" style="display: none;"><div class="form-group" style="padding-top: 1em;"><label class="col-sm-3 control-label" for="attname" id="attname"> Attribute name:</label><div class="col-sm-3"><input class="languagesetting form-control" type="text" value="" name="lang[' + lang + ']" id="lang_' + lang + '"></div></div></div></div>');

            // Reload tabs
            $('#tabs').tabs('refresh');

            // Open new tab
            $('#tabs').tabs('option', 'active', num_tabs);

            // Set the active-class on the new tab
            $('#tabs ul li').removeClass('active');
            $('#tabs ul li:last-child').addClass('active');

            // Remove the language from select
            $("select#langdata option[value='"+$("#langdata").val()+"']").remove();
        }
    });

    if($("#attribute_type").val() == "DD")
        {
            $("#dd").show();
        }
    else
        {
            $("#dd").hide();
        }

    $("#attribute_type").change(function() {
        if($("#attribute_type").val()=="TB" || $("#attribute_type").val()=="DP")
            {
                $("#dd").hide();
            }
        else if($("#attribute_type").val()=="DD")
            {
                $("#dd").show();
            }
        else
            {
                $("#dd").hide();
            }
    });
    $('#add').effect('pulsate', {times: 2}, 1000);
    var id = 1;
    $('#add_new_attribute').click(function(){
            html = "<tr>"+
            "<td colspan='2'><input type='text' name='attribute_value_name_"+id+"' id='attribute_value_name_"+id+"' size='30'></td></tr>";
                  $('.dd').fadeIn('slow');
        $('#ddtable tr:last').after(html);
        id++;
    });
    $(document.body).on('dblclick', '.editable', function() {
        editAttrValue(this.id);
    });
    $('.actions .edit').click(function(ev) {
        editAttrValue($(this).attr('name'));
    });

    $('.actions .cancel').click(function(){
	   var thisRow = $(this).closest('tr');
	   var valueText = $('td.data', thisRow).html('<div id="'+$('td.data', thisRow).attr('data-id')+'" class="editable">'+$('td.data', thisRow).attr('data-text')+'</div>');
		$('.actions .cancel', thisRow).hide();
		$('.actions .edit, .actions .delete', thisRow).show();
    });

    /**
     * @todo Doc
     */
    function editAttrValue(valueId) {
        var valueText = $.trim($("#"+valueId).text());
        var thisRow = $("#"+valueId).closest('tr');
        $("#"+valueId).replaceWith( "<div><input type='text' size='30' name='editbox' id='editbox"+valueId+"' /><input type='hidden' id='value_id' name='value_id' value='"+valueId+"' /></div>" );
        $('#editbox'+valueId).val(valueText);
        $('.actions .edit, .actions .delete', thisRow).hide();
        $('.actions .cancel', thisRow).show();
    }

    $('.languagesetting').click(function(){
        $(".languagesetting").css('border', '1px solid black');
        $(".languagesetting").css('background-color', 'white');
    })

    // Hide all cancel-buttons
    $('.cancel').hide();
});


