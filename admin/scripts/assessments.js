function jquery_goodchars(e, goods)
{
   var key, keychar;
   key = e.which;
   if (key == null) return true;

   // get character
   keychar = String.fromCharCode(key);
   keychar = keychar.toLowerCase();
   goods = goods.toLowerCase();

   // check goodkeys
   if (goods.indexOf(keychar) != -1)
        return true;

   // control keys
   if ( key==null || key==0 || key==8 || key==9  || key==27 )
      return true;

   // else return false
   return false;
}


$(document).ready(function(){
    $('#languagetabs').tabs();
    if ($(".assessmentlist tbody tr").size()>0)
    {
        $(".assessmentlist").tablesorter({sortList: [[0,0]] });
    }
    $('#radiototal,#radiogroup').change(
        function()
        {
              if ($('#radiototal').attr('checked')==true)
              {
                $('#gid').attr('disabled','disabled');
              }
              else
              {
                if ($('#gid>option').length==0){
                  $('#radiototal').attr('checked',true);
                  alert (strnogroup);    
                }
                else
                {
                    $('#gid').attr('disabled',false);
                }
              }
        }
    )
    $('#radiototal,#radiogroup').change();
    $('.numbersonly').keypress(
        function(e){
            return jquery_goodchars(e,'1234567890-');    
        }
    )
  }
);


