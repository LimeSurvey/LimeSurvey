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
 $('#copyconditions').submit(function() {
        if (!$('input[@id=cbox{$rows[cid]}]:checked').length) 
        {
         alert("Please select alteast one condition to copy from"); 
         return false;  
        } 
        if (!$('#copytomultiselect option:selected').length) 
        { 
            alert("Please select alteast one question to copy condition to","js");
            return false;  
        }
});
    //$('#languagetabs').bootTabs();
    $('#radiototal,#radiogroup').change(
        function()
        {
              if ($('#radiototal').attr('checked')==true)
              {
                $('#newgroupselect').attr('disabled','disabled');
              }
              else
              {
                if ($('#newgroupselect>option').length==0){
                  $('#radiototal').attr('checked',true);
                  alert (strnogroup);    
                }
                else
                {
                    $('#newgroupselect').attr('disabled',false);
                }
              }
        }
    );
    $('#radiototal,#radiogroup').change();
    $('.numbersonly').keypress(
        function(e){
            return jquery_goodchars(e,'1234567890-');    
        }
    );
  }
 
);

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

function selectTabFromOper() {
	var val = $('#method').val();
	if(val == 'RX') {
		$('#conditiontarget').bootTabs('enable', 4);
		$('#conditiontarget').bootTabs('option','active', 4);
		$('#conditiontarget').bootTabs('disable', 0);
		$('#conditiontarget').bootTabs('disable', 1);
		$('#conditiontarget').bootTabs('disable', 2);
		$('#conditiontarget').bootTabs('disable', 3);
	}
	else {
		$('#conditiontarget').bootTabs('enable', 0);
		$('#conditiontarget').bootTabs('enable', 1);
		$('#conditiontarget').bootTabs('enable', 2);
		if (!isAnonymousSurvey) $('#conditiontarget').bootTabs('enable', 3);
		$('#conditiontarget').bootTabs('option','active', 0);
		$('#conditiontarget').bootTabs('disable', 4);
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

	// At editing, a hidden field gives the Tab that should be selected
	// Louis : that suppose to be a numerical input not a string !!!
	if ($('#editTargetTab').val() != '') {
		$('#conditiontarget').bootTabs('option','active', $('#editTargetTab').val());
	}

	// At editing, a hidden field gives the Tab that should be selected
	if ($('#editSourceTab').val() != '') {
        var val = $('#editSourceTab').val();

        // Only two tabs: SRCPREVQUEST, SRCTOKENATTRS
        var nr = (val === '#SRCPREVQUEST' ? 0 : 1);
		$('#conditionsource').bootTabs('option','active', val);
		$('#conditionsource').tabs({active: nr});
	}
	
	// At editing, if cquestions is set, populate answers
	if ($('#cquestions').val() != '') {
		populateCanswersSelect(null);
	}
	
	$('#conditiontarget').bootTabs('option','active', 1);
});



