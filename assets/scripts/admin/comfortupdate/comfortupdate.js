$(document).on('ready  pjax:scriptcomplete', function(){
    // If no step is required, then the checkupdates buttons is display by php controler.
    // When user click on this button, it build the comfort updater buttons.
    //$("#ajaxcheckupdate").buildComfortButtons();
    $("#update_tab").buildComfortButtons();

    // First, we check if a particular step is required by the php controller
    step = $('#update_step').val();

    if( $.inArray( step , [ "newKey", "welcome", "checkFiles", "checkLocalErrors", "updatebuttons" ] ) != -1 ){
        $('#updaterWrap').displayComfortStep({'step' : step});
    }

});


