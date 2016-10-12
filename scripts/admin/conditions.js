// Louis : We need to overwrite jQuery tabs to make it compatible with bootstrap
(function($)
{
	jQuery.fn.bootTabs=function(fonc, value, ovalue)
	{
		this.each(function()
		{
			if(fonc=='enable')
			{
				value = value + 1;
				$tab = $(this).children('ul').children(':nth-child('+value+')');
				$tab.removeClass("disabled");
				$tab.children('a').attr('data-toggle', 'tab');
			}
			if(fonc=='disable')
			{
				value = value + 1;
				$tab = $(this).children('ul').children(':nth-child('+value+')');
				$tab.addClass("disabled");
				$tab.children('a').removeAttr('data-toggle');
			}
			
			if(fonc=='option')
			{
				if(value == 'active')
				{
					if($.isNumeric(ovalue))
					{
						$(this).children('ul').children(':nth-child('+ovalue+')').children('a').tab('show');
					}
					else
					{
						$('a[href$="'+ovalue+'"]').tab('show');						
					}
				}
			}
			
		});
		return this;
	};
})(jQuery);

function jquery_goodchars(e, goods)
{
   var key, keychar;
   key = e.which;
   if (key == null) return true;

   // get character
   keychar = String.fromCharCode(key);
   keychar = keychar.toLowerCase();
   goods = goods.toLowerCase();

   // check goodkeys
   if (goods.indexOf(keychar) != -1)
        return true;

   // control keys
   if ( key==null || key==0 || key==8 || key==9  || key==27 )
      return true;

   // else return false
   return false;
}


$(document).ready(function(){

    // TODO: Localization
    $('#copyconditions').submit(function() {
        if (!$('input[@id=cbox{$rows[cid]}]:checked').length) {
            alert("Please select alteast one condition to copy from");
            return false;
        } 
        if (!$('#copytomultiselect option:selected').length) {
            alert("Please select alteast one question to copy condition to","js");
            return false;
        }
    });

     //$('#languagetabs').bootTabs();
    $('#radiototal,#radiogroup').change(function() {
        if ($('#radiototal').attr('checked')==true) {
            $('#newgroupselect').attr('disabled','disabled');
        }
        else {
            if ($('#newgroupselect>option').length==0) {
                $('#radiototal').attr('checked',true);
                alert(strnogroup);
            }
            else {
                $('#newgroupselect').attr('disabled',false);
            }
        }
    });

    $('#radiototal, #radiogroup').change();

    $('.numbersonly').keypress(function(e) {
        return jquery_goodchars(e,'1234567890-');
    });

});

function populateCanswersSelect(evt) {
	var fname = $('#cquestions').val();
	// empty the canswers Select
	$('#canswers option').remove();
	var Keys = new Array();
	// store the indices in the Fieldnames array (to find codes and answers) where fname is found
	for (var i=0;i<Fieldnames.length;i++) {
		if (Fieldnames[i] == fname) {
			Keys[Keys.length]=i;
		}
	}

	for (var i=0;i<QFieldnames.length;i++) {
		if (QFieldnames[i] == fname) {
			$('#cqid').val(Qcqids[i]);
			if (Qtypes[i] == 'P' || Qtypes[i] == 'M')
			{
				$('#conditiontarget').bootTabs('enable', 0);
				$('#conditiontarget').bootTabs('option','active', 0);
				$('#conditiontarget').bootTabs('enable', 1);
				$('#conditiontarget').bootTabs('disable', 2);
				$('#conditiontarget').bootTabs('disable', 3);
				$('#conditiontarget').bootTabs('disable', 4);
				if ($('#method').val() != '==' || $('#method').val() != '!=')
				{
					$('#method').val('==');
				}
				$('#method option').not("[value='==']").not("[value='!=']").attr('disabled','disabled');
			}
			else
			{
				$('#conditiontarget').bootTabs('enable', 0);
				$('#conditiontarget').bootTabs('enable', 1);
				$('#conditiontarget').bootTabs('enable', 2);
				if (!isAnonymousSurvey) $('#conditiontarget').bootTabs('enable', 3);
				$('#conditiontarget').bootTabs('enable', 4);
				selectTabFromOper();
				$('#method option').removeAttr('disabled');
			}
		}
	}

	for (var i=0;i<Keys.length;i++) {
		var optionSelected = false;
		// If we are at page load time, then we may know which option to select
		if (evt === null)
		{ // Let's read canswersToSelect and check if we should select the option
			var selectedOptions = $('#canswersToSelect').val().split(';');
			for (var j=0;j<selectedOptions.length;j++) {
				if (Codes[Keys[i]] == selectedOptions[j])
				{
					optionSelected = true;
				}
			}
		}
		document.getElementById('canswers').options[document.getElementById('canswers').options.length] = new Option(Codes[Keys[i]]+' ('+Answers[Keys[i]]+')', Codes[Keys[i]],false,optionSelected);
	}
}

/**
 * When user selects method in "Comparison operator", we need to
 * disable/enable regexp and activate the tab.
 */
function selectTabFromOper() {
	var val = $('#method').val();
	if(val == 'RX') {
        $('a[href="#CANSWERSTAB"]').parent().addClass('disabled');
        $('a[href="#CONST"]').parent().addClass('disabled');
        $('a[href="#PREVQUESTIONS"]').parent().addClass('disabled');
        $('a[href="#TOKENATTRS"]').parent().addClass('disabled');
        $('a[href="#REGEXP"]').parent().removeClass('disabled');
        $('a[href="#REGEXP"]').trigger('click');
	}
	else {
		//if (!isAnonymousSurvey) $('#conditiontarget').bootTabs('enable', 3);

        $('a[href="#CANSWERSTAB"]').parent().removeClass('disabled');
        $('a[href="#CONST"]').parent().removeClass('disabled');
        $('a[href="#PREVQUESTIONS"]').parent().removeClass('disabled');
        $('a[href="#TOKENATTRS"]').parent().removeClass('disabled');
        $('a[href="#REGEXP"]').parent().addClass('disabled');

        // If regexp tab is selected, trigger click on first tab instead
        if ($('a[href="#REGEXP"]').parent().hasClass('active')) {
            $('a[href="#CANSWERSTAB"]').trigger('click');
        }
	}
}

$(document).ready(function(){

    // We must run this to enable the tabsactivate event
    $('#conditiontarget, #conditionsource').tabs();

	$('#conditiontarget').on('tabsactivate', function(event, ui) {
		$('#editTargetTab').val('#' + ui.newPanel.prop("id"));	
	});


	$('#conditionsource').on('tabsactivate', function(event, ui) {
		$('#editSourceTab').val('#' + ui.newPanel.prop("id"));	
	});

	// disable RegExp tab onload (new condition)
	
	$('#conditiontarget').bootTabs('disable', 4);
	// disable TokenAttribute tab onload if survey is anonymous
	if (isAnonymousSurvey) $('#conditiontarget').bootTabs('disable', 3);
	if (isAnonymousSurvey) $('#conditionsource').bootTabs('disable', 1);

	$('#resetForm').click( function() {
		$('#canswers option').remove();
		selectTabFromOper();
		$('#method').find('option').each( function() {
			$(this).attr('disabled','');
		});
		
	});

	$('#conditiontarget').find(':input').change(
		function(evt)
		{
			$('#conditiontarget').find(':input').each(
				function(indx,elt)
				{
					if (elt.id != evt.target.id)
					{
						if ($(elt).attr('type') == 'select-multiple' || 
							$(elt).attr('type') == 'select-one' ) {
							$(elt).find('option:selected').removeAttr("selected");
						}
						else {
							$(elt).val('');	
						}
					}
					return true;
				}
			);
		}
	);

	$('#conditionsource').find(':input').change(
		function(evt)
		{
			$('#conditionsource').find(':input').each(
				function(indx,elt)
				{
					if (elt.id != evt.target.id)
					{
						if ($(elt).attr('type') == 'select-multiple' || 
							$(elt).attr('type') == 'select-one' ) {
							$(elt).find('option:selected').removeAttr("selected");
						}
						else {
							$(elt).val('');	
						}
					}
					return true;
				}
			);
			if (evt.target.id == 'csrctoken')
			{
				$('#canswers option').remove();
			}
		}
	);

	// Select the condition target Tab depending on operator
        //selectTabFromOper($('#method').val());
	$('#method').change(selectTabFromOper);

	$('#cquestions').change(populateCanswersSelect);

	$('#csrctoken').change(function() {
		$('#cqid').val(0);
	});
	
	// At editing, if cquestions is set, populate answers
	if ($('#cquestions').val() != '') {
		populateCanswersSelect(null);
	}
	
    $('.nav-tabs').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    })

    // Tab management for add/edit condition
    var editTargetTab = $('input[name="editTargetTab"]').val();
    var editSourceTab = $('input[name="editSourceTab"]').val();
    $('a[href="' + editTargetTab + '"]').trigger('click');
    $('a[href="' + editSourceTab + '"]').trigger('click');

    // When user clicks tab, update hidden input
    $('.src-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="editSourceTab"]').val(href);
    });

    // When user clicks tab, update hidden input
    $('.target-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="editTargetTab"]').val(href);
    });

    // Disable clicks on disabled tabs (regexp)
    $(".nav-tabs a[data-toggle=tab]").on("click", function(e) {
        if ($(this).parent().hasClass("disabled")) {
            e.preventDefault();
            return false;
        }
    });

});

/**
 * Used when user clicks 'Add scenario' to replace default with number input
 * @return
 */
function scenarioaddbtnOnClickAction() {
    $('#scenarioaddbtn').hide();
    $('#defaultscenariotxt').hide('slow');
    $('.add-scenario-column').removeClass('col-sm-4').addClass('col-sm-2');
    $('#scenario').show('slow');
}
