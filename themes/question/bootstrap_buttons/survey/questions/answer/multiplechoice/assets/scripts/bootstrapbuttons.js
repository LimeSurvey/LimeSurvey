/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 *
 * This javascript updates value of "other" input field, shows "other" input field when "Other" checkbox button is checked
 * and triggers validation
 */



$( document ).ready(function() {
	$(".bootstrap-checkbox-value").each( function( index, element ){
		name = $(this).data('name');
		if ($(this).val()){
		    // "other" input field
		    $("#answer" + name).val($(this).val());
		    $("#" + name + "-div").removeClass('hide');
	    }
        // execute validation
	    checkconditions($("#answer" + name).val(), name, this.type);
	});

	$(".bootstrap-checkbox").change(function(){
		name = $(this).data('name');
		// conditionaly show or hide "other" input field
	    if ($(this).is(':checked')){
	    	$("#" + name + "-div").removeClass('hide');
	    } else {
	    	$("#" + name + "-div").addClass('hide');
	    	$("#answer" + name + "othertext").val('');
	    }
	});

	$(".bootstrap-input").on('change keyup paste', function(){
        name = $(this).data('name');
        checkconditions(this.value, this.name, this.type);
	});



	
});
