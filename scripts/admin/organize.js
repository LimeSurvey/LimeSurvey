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
        $('#orgdata').val($('ol.organizer').nestedSortable('serialize'));
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
				var questionsolID = "ol[id='gol_" + $(this).data('view')  + "']";

				var status = $(questionsolID).css("display");

				if(status=='none')
				{
					$(questionsolID).css("display","block");
				} 
				else
				{
					$(questionsolID).css("display","none");
				}

		}

	});

});