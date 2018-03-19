$(document).on('ready  pjax:scriptcomplete', function(){	
		if($('.side-body').length){
			//$('#update-container').removeClass();
			
			$('#notif-container .alert').attr('style', 'margin-top: 20px');			
			$('#notif-container .alert').prependTo('.side-body');
		}

		if($('.login-content').length){
			$('#notif-container .alert').prependTo('.login-content-form');
		}


//		window.setTimeout(function() { $("#notif-container .alert").alert('close'); }, 2000);

});