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

});

	function xmarkAllQuestions(xmode)
	{
		var elements = document.getElementsByTagName('input');

		switch (xmode)
		{
			case 0:
				for(i=0;i<elements.length;i++)
				{
					var chkName = elements[i].name;
					var chkTags = chkName.split('_');
				
					if (chkTags[0]=='qMark' || chkTags[0]=='gMark')
					{
						elements[i].checked = false;
					}
				}
			break;
			
			case 1:
				for(i=0;i<elements.length;i++)
				{
					var chkName = elements[i].name;
					var chkTags = chkName.split('_');
				
					if (chkTags[0]=='qMark' || chkTags[0]=='gMark')
					{
						elements[i].checked = true;
					}
				}
			break;
			
			case 2:
				for(i=0;i<elements.length;i++)
				{
					var chkName = elements[i].name;
					var chkTags = chkName.split('_');
				
					if (chkTags[0]=='qMark')
					{
						elements[i].checked = (elements[i].checked==true) ? false : true;
					} else 
					{
						if (chkTags[0]=='gMark')
							elements[i].checked = false;
					}
				}
			break;
		}
	}

	function xmarkGroupQuestions(groupName) 
	{
		var elements = document.getElementsByTagName('input');
		var chkGroup = document.getElementsByName(groupName);
		var groupID  = groupName.split('_')[1];

		for(i=0;i<elements.length;i++)
		{
			var chkName = elements[i].name;
			var chkTags = chkName.split('_');
			
			if (chkTags[0]=='qMark' && chkTags[1]==groupID)
			{
				elements[i].checked = chkGroup[0].checked;
			}
		}
	}

	function xMinMaxGroup(gID)
	{
		if(gID==0 || gID==-1)
		{

			var elements = document.getElementsByTagName('ol');

			for(i=0;i<elements.length;i++)
			{
				var olID = elements[i].id;
				var olTags = olID.split('_');
			
				if (olTags[0]=='gol')
				{
					if (gID==0) {elements[i].style.display='none';} else {elements[i].style.display='block';}
				}
			}
			
		} else
		{
			var ol = document.getElementById(gID);
			if(ol.style.display=='block')
			{
				ol.style.display='none';
			} else
			{
				ol.style.display='block';
			}
		}
	}
