$(document).ready(function(){
	// First, we check if a particular step is required by the php controller  
	step = $('#update_step').val();
	if( $.inArray( step , [ "newKey", "welcome", "checkFiles", "checkLocalErrors" ] ) != -1 ){
		$('#updaterWrap').displayComfortStep({'step' : step});
	}

	// If no step is required, then the checkupdates buttons is display by php controler. 
	// When user click on this button, it build the comfort updater buttons. 
	$("#ajaxcheckupdate").buildComfortButtons(); 
});


