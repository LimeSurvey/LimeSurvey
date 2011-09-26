$(document).ready(function() {
$.each(removeitem, function(index, value) { 
  $("select#langdata option[value='"+value+"']").remove();
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
         $("#addsign").click(function(){
         var lang = $("#langdata").val();
         if(lang != "")
            {
                $('#tabs').append("<div id='"+lang+"'><center>"+attname+"<input type='text' name='"+lang+"' id='"+lang+"' /></center><br></div>");
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
        
        "<td><input type='text' name='attribute_value_name_"+id+"' id='attribute_value_name_"+id+"' size='8' style='50%;'></td></tr>";
              $('.dd').fadeIn('slow');
    $('#ddtable tr:last').after(html);
    id++;
        });
        $(".editable").click(function () {
        var value_id = this.id;
        $(this).replaceWith( "<div><input type='text' size='20' name='editbox' id='editbox' value="+$(this).text()+"><input type='hidden' id='value_id' name='value_id' value='"+value_id+"' /></div>" );
});
$('#edit').click(function(){
       var value_id = this.name;
       $("#"+value_id).replaceWith( "<div><input type='text' size='20' name='editbox' id='editbox' value="+$("#"+value_id).text()+"><input type='hidden' id='value_id' name='value_id' value='"+value_id+"' /></div>" );    
});
});


  