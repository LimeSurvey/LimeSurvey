/**
 * @file array-number-checkbox Array (number) checkbox layout event system
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
 */

/**
 * Activate the answer value for expression manager in Array (number) checkbox layout
 */
function doArrayNumberCheckbox(){
  $(".array-multi-flexi .checkbox-list").on("change",":checkbox",function(){
    var name=$(this).attr("name");
    if($(this).is(":checked")){
      $("#answer"+name).val($(this).attr("value"));
      $("#java"+name).val($(this).attr("value"));
      fixnum_checkconditions($(this).attr("value"), name, 'text', 'keyup', true);
    }else{
      $("#answer"+name).val("");
      $("#java"+name).val("");
      fixnum_checkconditions("", name, 'text', 'keyup', true);
    }
  });
}
