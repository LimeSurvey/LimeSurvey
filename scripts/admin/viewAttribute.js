$(document).ready(function() {
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
            var num_tabs = $('#tabs ul li').length + 3;  // TODO: Why start counting on 3?

            // Add li anchor and content
            $('#tabs ul').append(
                '<li class="ui-state-default ui-corner-top" role="tab" tabindex="-1" aria-controls="' + lang + '" aria-labelledby="ui-id-' + num_tabs + '"aria-selected="fase"><a class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-' + num_tabs + '"href="#' + lang + '">' + $('#langdata option:selected').text() + '</a></li>'
            );
            $('#tabs').append('<div class="commonsettings"><div id="' + lang + '" aria-labelledby="ui-id-' + num_tabs + '" class="ui-tabs-panel ui-widget-content ui-corner-bottom" role="tabpanel" aria-expanded="true" aria-hidden="true" style="display: none;"> <table width="400" class="nudgeleft"> <tbody><tr> <th> <label for="attname" id="attname"> Attribute name:                            </label> </th> </tr> <tr> <td class="data"> <input class="languagesetting" style="border: 1px solid black; background-color: rgb(255, 255, 255);" type="text" value="" name="lang[' + lang + ']" id="lang_' + lang + '">                        </td> </tr> </tbody></table> </div> </div>');

            // Reload tabs
            $('#tabs').tabs('refresh');

            // Open new tab
            $('#tabs').tabs('option', 'active', num_tabs - 3);

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
    $('.add').click(function(){
            html = "<tr>"+
            "<td colspan='2'><input type='text' name='attribute_value_name_"+id+"' id='attribute_value_name_"+id+"' size='8' style='50%;'></td></tr>";
                  $('.dd').fadeIn('slow');
        $('#ddtable tr:last').after(html);
        id++;
    });
    $(document.body).on('dblclick', '.editable', function() {
        editAttrValue(this.id);
    });
    $('.actions .edit').click(function(){
       editAttrValue(this.name);
    });
    $('.actions .cancel').click(function(){
	   var thisRow = $(this).closest('tr');
	   var valueText = $('td.data', thisRow).html('<div id="'+$('td.data', thisRow).attr('data-id')+'" class="editable">'+$('td.data', thisRow).attr('data-text')+'</div>');
		$('.actions .cancel', thisRow).hide();
		$('.actions .edit, .actions .delete', thisRow).show();
    });
	function editAttrValue(valueId) {
	   var valueText = $.trim($("#"+valueId).text());
	   var thisRow = $("#"+valueId).closest('tr');
       $("#"+valueId).replaceWith( "<div><input type='text' size='20' name='editbox' id='editbox"+valueId+"' /><input type='hidden' id='value_id' name='value_id' value='"+valueId+"' /></div>" );
		$('#editbox'+valueId).val(valueText);
		$('.actions .edit, .actions .delete', thisRow).hide();
		$('.actions .cancel', thisRow).show();
	}
    $('.languagesetting').click(function(){
        $(".languagesetting").css('border', '1px solid black');
        $(".languagesetting").css('background-color', 'white');
    });
});


