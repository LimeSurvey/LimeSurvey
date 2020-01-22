/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Update column and line with sum in a text table
 *
 * @param {ids} if of the table
 * @param {_radix} number seperator
 */

function navigator_countdown(n)
{
	$('button.disabled').prop("disabled", true);// Double check : already in navbuttonsJqueryUi
    $(window).data('countdown', n);
    navigator_countdown_btn().each(function(i, e)
    {
        $(e).data('text', $(e).text());
    });
    navigator_countdown_int();
}

function navigator_countdown_btn()
{
	return $('.ls-move-btn');
}

function navigator_countdown_end()
{
	navigator_countdown_btn().each(function(i, e)
	{
		$(e).prop("disabled",false);
		if($(e).is(".ui-button" )){
			$(e).button("option", "disabled", false );
		}
		$(e).removeClass("disabled");
		if($(e).find('.ui-button-text').length){
			$(e).find('.ui-button-text').html( $(e).data('text'));
		}else{
			$(e).html($(e).data('text'));
		}
	});
	$(window).data('countdown', null);
}

function navigator_countdown_int()
{
	var n = $(window).data('countdown');
	if(n)
	{
		navigator_countdown_btn().each(function(i, e)
		{
			if($(e).find('.ui-button-text').length){
				$(e).find('.ui-button-text').html( $(e).data('text'));
				// just count-down for delays longer than 1 second
				if(n > 1) $(e).find('.ui-button-text').html( $(e).data('text')+ " (" + n + ")");
			}else{
				$(e).html($(e).data('text'));
				if(n > 1) $(e).html( $(e).data('text')+ " (" + n + ")");
			}
		});
		$(window).data('countdown', --n);
	}
	window.setTimeout((n > 0? navigator_countdown_int: navigator_countdown_end), 1000);
}

