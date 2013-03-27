// surveytoolbar.js

$(document).ready(function(){
    $(".saveandreturn").click(function() {
        var form=$(this).parents('form:first'); //Get the parent form info
        $("#newpage").val('return');
        form.submit();
    });
});
