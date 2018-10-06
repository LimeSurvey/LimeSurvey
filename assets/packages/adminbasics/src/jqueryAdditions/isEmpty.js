$.fn.extend({
    isEmpty: function(helperMsg){
        if($.trim($(this).value).length == 0){
            alert(helperMsg);
            $(this).focus(); // set the focus to this input
            return false;
        }
        return true;
    }
});
export {};