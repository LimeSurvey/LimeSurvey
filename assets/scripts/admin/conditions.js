
// Namespace
var LS = LS || {  onDocumentReady: {} };

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


$(document).on('ready  pjax:scriptcomplete', function(){

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
	$('#resetForm').click( function() {
		$('#canswers option').remove();
		selectTabFromOper();
		$('#method').find('option').each( function() {
			$(this).attr('disabled','');
		});
		
	});

	// Select the condition target Tab depending on operator
	$('#method').change(selectTabFromOper);
	$('#quick-add-method').change(quickAddSelectTabFromOper);

    var p = new populateCanswersSelectObject();
    populateCanswersSelect = p.fun;

	$('#cquestions').change(populateCanswersSelect);

    // Populate stuff for quick-add modal
    var p2 = new populateCanswersSelectObject();
    p2.cquestionsId      = '#quick-add-cquestions';
    p2.canswersId        = '#quick-add-canswers';
    p2.canswersIdNoHash  = 'quick-add-canswers';
    p2.cqid              = '#quick-add-cqid';
    p2.conditiontargetId = '#quick-add-conditiontarget';
    p2.methodId          = '#quick-add-method';
    p2.canswersToSelectId= '#quick-add-canswersToSelectId';
	$('#quick-add-cquestions').change(p2.fun);
	
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
    $('#editconditions .src-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="editSourceTab"]').val(href);
    });

    // When user clicks tab, update hidden input
    $('#editconditions .target-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="editTargetTab"]').val(href);
    });

    // Tab management for quick-add modal
    var editTargetTab = $('input[name="quick-add-editTargetTab"]').val();
    var editSourceTab = $('input[name="quick-add-editSourceTab"]').val();
    $('a[href="' + editTargetTab + '"]').trigger('click');
    $('a[href="' + editSourceTab + '"]').trigger('click');

    // When user clicks tab, update hidden input
    $('#quick-add-conditions-form .src-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="quick-add-editSourceTab"]').val(href);
    });

    // When user clicks tab, update hidden input
    $('#quick-add-conditions-form .target-tab').on('click', function(e) {
        var href = $(e.currentTarget).find('a').attr('href');
        $('input[name="quick-add-editTargetTab"]').val(href);
    });

    // Disable clicks on disabled tabs (regexp)
    $(".nav-tabs a[data-toggle=tab]").on("click", function(e) {
        if ($(this).parent().hasClass("disabled")) {
            e.preventDefault();
            return false;
        }
    });

    // Bind save-buttons in quick-add modal
    $('#quick-add-condition-save-button').on('click', function(ev) {
        var formData = $('#quick-add-conditions-form').serializeArray();
        var url = $('#quick-add-url').html();
        console.ls.log('formData', formData);
        LS.ajax({
            url: url,
            data: formData,
            method: 'POST',
            error: function () {
                console.ls.log(arguments);
            }
        });
    });

    // Save-and-close for quick-add modal
    $('#quick-add-condition-save-and-close-button').on('click', function(ev) {
        var formData = $('#quick-add-conditions-form').serializeArray();
        var url = $('#quick-add-url').html();
        LS.ajax({
            url: url,
            data: formData,
            method: 'POST',
            success: function () {
                location.reload();
            },
            error: function () {
                console.ls.log(arguments);
            }
        });
    });

    // Close for quick-add modal
    $('#quick-add-condition-close-button').on('click', function(ev) {
        location.reload();
    });
});

/**
 * Object with one public variable: fun, which is
 * the populateCanswersSelect function.
 * @constructur
 */
populateCanswersSelectObject = function() {

    // Default values for the original add/edit form
    // They will be overrided by the quick-add form
    this.cquestionsId      = '#cquestions';
    this.canswersId        = '#canswers';
    this.canswersIdNoHash  = 'canswers';
    this.cqid              = '#cqid';
    this.conditiontargetId = '#conditiontarget';
    this.methodId          = '#method';
    this.canswersToSelectId= '#canswersToSelect';

    var that = this;

    this.fun = function(evt) {

        // preselect the first option if select object value is null
        if ($(that.cquestionsId).val() === null){
            $(that.cquestionsId+" option:first").attr('selected','selected');
        }

        var fname = $(that.cquestionsId).val();
        // empty the canswers Select
        $(that.canswersId + ' option').remove();
        var Keys = new Array();
        // store the indices in the Fieldnames array (to find codes and answers) where fname is found
        for (var i=0;i<Fieldnames.length;i++) {
            if (Fieldnames[i] == fname) {
                Keys[Keys.length]=i;
            }
        }

        for (var i=0;i<QFieldnames.length;i++) {
            if (QFieldnames[i] == fname) {
                $(that.cqid).val(Qcqids[i]);
                if (Qtypes[i] == 'P' || Qtypes[i] == 'M')
                {
                    //$(that.conditiontargetId).bootTabs('enable', 0);
                    //$(that.conditiontargetId).bootTabs('option','active', 0);
                    //$(that.conditiontargetId).bootTabs('enable', 1);
                    //$(that.conditiontargetId).bootTabs('disable', 2);
                    //$(that.conditiontargetId).bootTabs('disable', 3);
                    //$(that.conditiontargetId).bootTabs('disable', 4);
                    if ($(that.methodId).val() != '==' || $('#method').val() != '!=')
                    {
                        $(that.methodId).val('==');
                    }
                    $(that.methodId + ' option').not("[value='==']").not("[value='!=']").attr('disabled','disabled');
                }
                else
                {
                    //$(that.conditiontargetId).bootTabs('enable', 0);
                    //$(that.conditiontargetId).bootTabs('enable', 1);
                    //$(that.conditiontargetId).bootTabs('enable', 2);
                    //if (!isAnonymousSurvey) {
                        //$(that.conditiontargetId).bootTabs('enable', 3);
                    //}
                    //$(that.conditiontargetId).bootTabs('enable', 4);
                    selectTabFromOper();
                    $(that.methodId + ' option').removeAttr('disabled');
                }
            }
        }

        for (var i=0;i<Keys.length;i++) {
            var optionSelected = false;
            // If we are at page load time, then we may know which option to select
            if (evt === null) {
                // Let's read canswersToSelect and check if we should select the option
                var selectedOptions = $(that.canswersToSelectId).val().split(';');
                for (var j=0;j<selectedOptions.length;j++) {
                    if (Codes[Keys[i]] == selectedOptions[j]) {
                        optionSelected = true;
                    }
                }
            }
            document.getElementById(that.canswersIdNoHash).options[document.getElementById(that.canswersIdNoHash).options.length] = new Option(Codes[Keys[i]]+' ('+Answers[Keys[i]]+')', Codes[Keys[i]],false,optionSelected);
        }
    }
};

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

/**
 * Same as selectTabFromOper but for quick-add modal
 */
function quickAddSelectTabFromOper() {
	var val = $('#quick-add-method').val();
	if(val == 'RX') {
        $('a[href="#QUICKADD-CANSWERSTAB"]').parent().addClass('disabled');
        $('a[href="#QUICKADD-CONST"]').parent().addClass('disabled');
        $('a[href="#QUICKADD-PREVQUESTIONS"]').parent().addClass('disabled');
        $('a[href="#QUICKADD-TOKENATTRS"]').parent().addClass('disabled');
        $('a[href="#QUICKADD-REGEXP"]').parent().removeClass('disabled');
        $('a[href="#QUICKADD-REGEXP"]').trigger('click');
	}
	else {
		//if (!isAnonymousSurvey) $('#conditiontarget').bootTabs('enable', 3);

        $('a[href="#QUICKADD-CANSWERSTAB"]').parent().removeClass('disabled');
        $('a[href="#QUICKADD-CONST"]').parent().removeClass('disabled');
        $('a[href="#QUICKADD-PREVQUESTIONS"]').parent().removeClass('disabled');
        $('a[href="#QUICKADD-TOKENATTRS"]').parent().removeClass('disabled');
        $('a[href="#QUICKADD-REGEXP"]').parent().addClass('disabled');

        // If regexp tab is selected, trigger click on first tab instead
        if ($('a[href="#QUICKADD-REGEXP"]').parent().hasClass('active')) {
            $('a[href="#QUICKADD-CANSWERSTAB"]').trigger('click');
        }
	}
}


/**
 * Used when user clicks 'Add scenario' to replace default with number input
 * @return
 */
function scenarioaddbtnOnClickAction() {
    $('#defaultscenarioshow').hide('slow');
    $('.add-scenario-column').removeClass('col-sm-4').addClass('col-sm-2');
    $('#scenario').show('slow');
}

/**
 * Redirects to url
 * Button in scenario to add a condition for this scenario (prefill scenario number)
 * @param {string} url
 * @return
 */
function addConditionToScenario(url) {
    location = url + '#formHeader';
    return false;
}
