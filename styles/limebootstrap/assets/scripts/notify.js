$(document).ready(function(){	
		if($('.side-body').length){
			$('#notif-container .alert').attr('style', 'margin-top: 20px');			
			$('#notif-container .alert').prependTo('.side-body');
		}

		if($('.login-content').length){
			$('#notif-container .alert').prependTo('.login-content-form');
		}
});