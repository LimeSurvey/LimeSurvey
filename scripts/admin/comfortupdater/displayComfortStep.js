$.fn.displayComfortStep = function(options)
{
	// Will be used later for animation params
	var defauts={};  
	var params=$.extend(defauts, options); 

	$ajaxLoader = $("#ajaxContainerLoading");
	$ajaxLoader.show();

	$("#preUpdaterContainer").empty();
	$("#updaterLayout").show();
	$("#updaterContainer").show();

	
	
	
	// we display the ComfortUpdater tab inside the global setting view
	$( "#tabs" ).tabs( "option", "active", 8 );
	
	// We need to know the destination build to resume any step
	$destinationBuild = $('#destinationBuildForAjax').val();
	$access_token =  $('#access_tokenForAjax').val();
	$url = "";
	
	switch(params.step) {
	    case "newKey":
	        $url = 'update/sa/getnewkey';
	        break;
	    
	    case "checkFiles":
	    	$url = 'update/sa/fileSystem';
	    	break;
	    
	    case "checkLocalErrors":
	    	$url = 'update/sa/checkLocalErrors';
	    	break;
	    
	    case "welcome":
	        $url = 'update/sa/getwelcome';
	        break;
	      
	}
	
	$url += '?destinationBuild=' + $destinationBuild + '&access_token=' + $access_token;
	
	$.ajax({
	    url: $url, 
	    success: function(html) {
			// We hide the loader, and we append the submit new key content
			$ajaxLoader.hide();
	        $("#updaterContainer").empty().append(html);
	        
	        // Menus
	        $("#welcome").hide();
	        $("#newKey").show();
		
		},
		error :  function(html, statut){
			$("#preUpdaterContainer").empty();
			$("#updaterLayout").show();
			$("#updaterContainer").show();
			
			$("#updaterContainer").empty().append("<span class='error'>you have an error, or a notice, inside your local installation of limesurvey. See : <br/></span>");
			$("#updaterContainer").append(html.responseText);
		}
	});	
};