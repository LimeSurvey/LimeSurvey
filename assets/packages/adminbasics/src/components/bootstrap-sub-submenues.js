var activateSubSubMenues = function(){
	$('ul.dropdown-menu [data-bs-toggle=dropdown]').on('click', function(event) {
		event.preventDefault(); 
		event.stopPropagation(); 
		$(this).parent().siblings().removeClass('open');
		$(this).parent().toggleClass('open');
	});
};

export default activateSubSubMenues;
