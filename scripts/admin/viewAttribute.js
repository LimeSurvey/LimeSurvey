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
            $('#tabs').append("<div class='commonsettings'><div id='"+lang+"'><table width='400px' align='center' class='nudgeleft'><tr><th>"+attname+"</th></tr><tr><td><input type='text' name='"+lang+"' id='"+lang+"' style='border: 1px solid #ccc' class='languagesetting' /></td></tr></table></div></div>");
//            $('#tabs').append("<div id='"+lang+"'><center>"+attname+"<input type='text' name='"+lang+"' id='"+lang+"' /></center><br></div>");
            $("#tabs").tabs("add","#"+lang,$("#langdata option:selected").text());
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


