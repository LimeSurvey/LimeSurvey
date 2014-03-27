$(document).ready(function(){
    var sourceItem;
    $('ol.organizer').nestedSortable({
		doNotClear: true,
        disableNesting: 'no-nest',
        forcePlaceholderSize: true,
        handle: 'div',
        helper: 'clone',
        items:  'li',
        maxLevels: 2,
        opacity: .6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        rootID: 'root',
        stop: function(event, ui) {
			var itemLevel = $(ui.item).attr('data-level');
			var listLevel = $(ui.item).closest('ol').attr('data-level');
            if (itemLevel != listLevel) {
                $('ol.organizer').nestedSortable('cancel');
			}
        },
        change: function(event, ui) {
            if (typeof ui.item != 'undefined' && typeof ui.placeholder != 'undefined') {
				var itemLevel = $(ui.item).attr('data-level');
				var listLevel = $(ui.placeholder).closest('ol').attr('data-level');
                if (itemLevel != listLevel) {
                    $('.placeholder').addClass('ui-nestedSortable-error');
                }
                else {
                    $('.placeholder').removeClass('ui-nestedSortable-error');
                }
            }
        },
        tolerance: 'pointer',
        toleranceElement: '> div'
    });

    $('#btnSave').click(function(){
		// original line: 
		//$('#orgdata').val($('ol.organizer').nestedSortable('serialize') + "/hello world");
    
		//look for desired bulk action
		var tmp1 = $("#bulkaction").val();
	
		if (tmp1 != "...") {
	
			// look for checked checkboxes
			var tmp2 = '';
			$("input[data^='qMark']").each(function() {     
				if (this.checked) {
					tmp2 += '&'+this.value;
				}
			});
 
			$('#orgdata').val($('ol.organizer').nestedSortable('serialize') + "/" + tmp1 + "/" + tmp2);
		}	
		else {
			// no bulkaction required
			$('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
		}

        frmOrganize.submit(); 
    })

	// select, unselect or toggle selected groups an questions
	$("[data-select]").on('click',function(event){
		
		event.preventDefault();
		switch ($(this).data('select'))
		{
			case 'all':
				$("input[data^='qMark'],input[data^='gMark']").prop('checked', true);
			break;

			case 'none':
				$("input[data^='qMark'],input[data^='gMark']").prop('checked', false);
			break;

			case 'toggle':
				$("input[data^='qMark']").prop('checked', function(i,val) {return !val;});
				$("input[data^='gMark']").prop('checked', false);
			break;

				}

	});


	// if a group checkbox is checked/unchecked, check/uncheck all questions within this group
	$("input[data^='gMark']").on('click', function(event) {

		var status = $(this).is(':checked');
		var gTag = $(this).attr('data').split('_');
		var qTag = "input[data^='qMark_" + gTag[1] + "']";

		$(qTag).prop('checked', status);
	});

	
	// collapse groups to make questions invisible in this view or expand groups
	$("[data-view]").on('click',function(event){

		event.preventDefault();
		switch ($(this).data('view'))
		{
			case 'collapse':
				$("ol[id^='gol']").css("display","none");
			break;

			case 'expand':
				$("ol[id^='gol']").css("display","block");
			break;
			
			default:
				var groupID = "ol[id='gol_" + $(this).data('view')  + "']";

				var status = $(groupID).css("display");

				if(status=='none')
				{
					$(groupID).css("display","block");
				} 
				else
				{
					$(groupID).css("display","none");
				}

		}

	});
	
	// collapse or expand a question
	$("[data-questview]").on('click',function(event){

		event.preventDefault();

		switch ($(this).data('questview'))
		{
			case 'collapse':
				$("li[id^='list_q']").css("height","30px");
				$("li[id^='list_q']").css("overflow","hidden");
				$("img[id^='list_q']").css("display","block");
			break;
			
			case 'expand':
				$("li[id^='list_q']").css("height","");
				$("img[id^='list_q']").css("display","none");
			
			break;
			
			default:
				var questionID = "li[id='list_q" + $(this).data('questview') + "']";
				var questimgID = "img[id='list_q" + $(this).data('questview') + "']";

				var status = $(questionID).css("height");

				if(parseInt(status) > 30)
				{
					$(questionID).css("height", "30px");
					$(questionID).css("overflow", "hidden");
					$(questimgID).css("display", "block");
				}
				else
				{
					$(questionID).css("height", "");
					$(questimgID).css("display", "none");
				}
			break;
		}
	});

});