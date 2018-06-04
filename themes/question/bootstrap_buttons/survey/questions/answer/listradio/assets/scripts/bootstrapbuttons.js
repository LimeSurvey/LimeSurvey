/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 *
 * This javascript updates value of "other" input field, shows "other" input field when "Other" radio button is checked
 * and triggers validation
 */



$( document ).ready(function() {
	$(".bootstrap-radio-value").each( function( index, element ){
		if ($(this).val()){
		    name = $(this).data('name');
		    // "other" input field
		    $("#answer" + name + "othertext").val($(this).val());
		    $("#" + name + "-div").removeClass('hide');
		    // execute validation
		    checkconditions($(this).val(), name, this.type);
	    }
	});

	$(".bootstrap-radio").change(function(){
		name = $(this).attr('name');
		// conditionaly show or hide "other" input field
	    if ($(this).val() === '-oth-'){
	    	$("#" + name + "-div").removeClass('hide');
	    } else {
	    	$("#" + name + "-div").addClass('hide');
	    	$("#answer" + name + "othertext").val('');
	    }
	});
});