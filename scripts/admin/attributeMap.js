$(document).ready(function(){
    if(!$('#centralattribute').length ) { //Warning that there are no unmapped attributes to map
        alert(attributesMappedText);
    }
    var height = $(document).height();
    var width = $(document).width();
    var tokencurrentarray = {};
    var newcurrentarray = {};
    if($("#overwrite").is(':checked')) {var attoverwrite=true;} else {var attoverwrite=false;}
    if($("#overwriteman").is(':checked')) {var attoverwriteman=true;} else {var attoverwriteman=false;}
    if($("#createautomap").is(':checked')) {var attcreateautomap=true;} else {var attcreateautomap=false;}
    if($("#overwritest").is(':checked')) {var attoverwritest=true;} else {var attoverwritest=false;}

    $('#tokenattribute').css({'height' : height-200});
    $('#centralattribute').css({'height' : height-200});
    $('#newcreated').css({'height' : height-200});
	var ncHeadingHeight = $('#newcreated .heading').outerHeight();
	$('.newcreate').css({
		'padding-bottom':0,
		'min-height':$('#newcreated').height()-ncHeadingHeight-5
	});
	
    $("#overwrite").click(function(){
        if($("#overwrite").is(':checked')) {attoverwrite=true;} else {attoverwrite=false;}
    });
    $("#overwriteman").click(function(){
        if($("#overwriteman").is(':checked')) {attoverwriteman=true;} else {attoverwriteman=false;}
    });
    $("#overwritest").click(function(){
        if($("#overwritest").is(':checked')) {attoverwritest=true;} else {attoverwritest=false;}
    });
    $("#createautomap").click(function(){
        if($("#createautomap").is(':checked')) {attcreateautomap=true;} else {attcreateautomap=false;}
    });
    $(".newcreate").sortable({
            connectWith:'.tokenatt,#cpdbatt'}
    );
    $("#cpdbatt").sortable({
        connectWith:'.tokenatt,.newcreate,.standardfields',
        helper: 'clone',
        appendTo: 'body',
        receive: function(event,ui) {
            newcurrentarray = $(this).sortable('toArray');
            var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
            cpdbattpos = cpdbattpos+1;
            $('ul#cpdbatt > li:nth-child('+cpdbattpos+')').css("color", "black");
            $('ul#cpdbatt > li:nth-child('+cpdbattpos+')').css("background-color","white");
        }
    });
    $("ul.standardfields").sortable({
        helper: 'clone',
        appendTo: 'body',
        connectWith: "ul",
        beforeStop: function(event,ui) {
            $(this).sortable('cancel');
        },
        receive: function(event,ui) {
            tokencurrentarray = $(this).sortable('toArray');
            var tattpos = jQuery.inArray($(ui.item).attr('id'),tokencurrentarray);
            var cattpos = tattpos+1;
            var tattid = tokencurrentarray[cattpos-2];
            var cattid = $(ui.item).attr('id');
            if(tattpos == 0 ) {
                alert(mustPairAttributeText);
                $(ui.sender).sortable('cancel');
            }
            else if($("#"+tattid).css('color') == 'white') {
                alert(onlyOneAttributeMappedText);
                $(ui.sender).sortable('cancel');
            }
            else {
                $('ul.standardfields > li:nth-child('+tattpos+')').css("color","white");
                $('ul.standardfields > li:nth-child('+tattpos+')').css("border-top","0");
                $('ul.standardfields > li:nth-child('+cattpos+')').css("color","white");
                $('ul.standardfields > li:nth-child('+cattpos+')').css("margin-top","-5px");
                $('ul.standardfields > li:nth-child('+cattpos+')').css("border-top","0");
                $('ul.standardfields > li:nth-child('+cattpos+')').css("min-height","20px");
                $("#"+cattid).css("background-color","#696565");
                $("#"+tattid).css("background-color","#696565");
            }
        }
    });
    $("ul.tokenatt").sortable({
        helper: 'clone',
        appendTo: 'body',
        connectWith: "ul",
        beforeStop: function(event,ui) {
                $(this).sortable('cancel');
        },
        receive: function(event,ui) {
            tokencurrentarray = $(this).sortable('toArray');
            var tattpos = jQuery.inArray($(ui.item).attr('id'),tokencurrentarray);
            var cattpos = tattpos+1;
            var tattid = tokencurrentarray[cattpos-2];
            var cattid = $(ui.item).attr('id');
            if(tattpos == 0 ) {
                alert(mustPairAttributeText);
                $(ui.sender).sortable('cancel');
            }
            else if($("#"+tattid).css('color') == 'white') {
                alert(onlyOneAttributeMappedText);
                $(ui.sender).sortable('cancel');
            }
            else {
                $('ul.tokenatt > li:nth-child('+tattpos+')').css("color","white");
                $('ul.tokenatt > li:nth-child('+tattpos+')').css("border-top","0");
                $('ul.tokenatt > li:nth-child('+cattpos+')').css("color","white");
                $('ul.tokenatt > li:nth-child('+cattpos+')').css("margin-top","-5px");
                $('ul.tokenatt > li:nth-child('+cattpos+')').css("border-top","0");
                $('ul.tokenatt > li:nth-child('+cattpos+')').css("min-height","20px");
                $("#"+cattid).css("background-color","#696565");
                $("#"+tattid).css("background-color","#696565");
            }
        }
    });
    $("ul.newcreate").sortable({
        helper: 'clone',
        appendTo: 'body',
        dropOnEmpty: true,
        receive: function(event,ui) {
            if($(ui.item).attr('id')[0]=='t')
            {
                alert(cannotAcceptTokenAttributesText)
                $(ui.sender).sortable('cancel');
            }
                newcurrentarray = $(this).sortable('toArray');
                var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
                cpdbattpos = cpdbattpos+1;
                $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("color", "white");
                $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("background-color","#696565");
        }
    });
    $('#attmap').click(function() {
        var mappedarray = {};
        $.each(tokencurrentarray, function(index,value) {
            if(value[0]=='c') {
                    mappedarray[tokencurrentarray[index-1].substring(2)] = value.substring(2);
            }
        });
       $.each(newcurrentarray, function(index,value) {
            newcurrentarray[index] = value.substring(2);
        });
        $("#processing").dialog({
            height: 90,
            width: 50,
            modal: true
        });

    $("#processing").load(copyUrl, {
        mapped: mappedarray,
        newarr: newcurrentarray,
        surveyid: surveyId,
        overwrite: attoverwrite,
        overwriteman: attoverwriteman,
        overwritest: attoverwritest,
        participant_id : participant_id,
        createautomap: attcreateautomap
        }, function(msg){
            $(this).dialog("close");
            alert(msg);
            $(location).attr('href',redUrl);
        });
    });
});