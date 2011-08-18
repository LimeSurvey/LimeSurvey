$(document).ready(function() {
   $(":checkbox").change(function(){
       var visibleid = $(this).attr('id');
       $.post(saveVisible, 
       { attid : $(this).attr('id'),
         visiblevalue : $("#"+visibleid+":checked").val()},
       function(data) {});
   });
 
         
         
        $(".attid").hide();
        $('#add').effect('pulsate', { times: 2 }, 1000);
        var id = 1;
        $('.add').click(function(){
        html = "<tr><td>"+
        "<input type='text' name='attribute_name_"+id+"' id='attribute_name_"+id+"' size='8' style='50%;'></td>"+
        "<td><select id='attribute_type_"+id+"' name='attribute_type_"+id+"'>"+
        "<option value='DD'>Drop Down</option>"+
        "<option value='DP'>Date</option>"+
        "<option value='TB'>Text Box</option>"+
        "</select></td>"+
        "<td><input type='hidden' name='visible_"+id+"' value='FALSE' /><input type='checkbox' name='visible_"+id+"' value='TRUE' /></td> </tr>";
      $('.attid').fadeIn('slow');
    $('#atttable tr:last').after(html);
    id++;
    });
});