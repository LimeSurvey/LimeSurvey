$(document).ready(function(){
    if(!$('#tokenatt').children().length ) {
        alert(attributesMappedText);
    }
    var height = $(document).height();
    var width = $(document).width();
    var tokencurrentarray = {};
    var newcurrentarray = {};
    if($("#overwrite").is(':checked')) {var attoverwrite=true;} else {var attoverwrite=false;}
    if($("#overwriteman").is(':checked')) {var attoverwriteman=true;} else {var attoverwriteman=false;}
    if($("#createautomap").is(':checked')) {var attcreateautomap=true;} else {var attcreateautomap=false;}

    $('#tokenattribute').css({ 'height' : height-200});
    $('#centralattribute').css({ 'height' : height-200});
    $('#newcreated').css({ 'height' : height-200});
	var ncHeadingHeight = $('#newcreated .heading').outerHeight();
	$('.newcreate').css({
		'padding-bottom':0,
		'min-height':$('#newcreated').height()-ncHeadingHeight-5
	});
	var taHeadingHeight = $('#tokenattribute .heading').outerHeight();
	$('#tokenatt').css({
		'min-height':$('#tokenattribute').height()-taHeadingHeight-5
	});
	
    $("#tokenatt").sortable({ connectWith:'.centralatt,.newcreate',helper: 'clone',appendTo: 'body'});
    $("ul.centralatt").sortable({
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
            } else if($("#"+tattid).css('color') == 'rgb(204, 204, 204)') {
                alert(onlyOneAttributeMappedText);
                $(ui.sender).sortable('cancel');
            } else {
                $('ul.centralatt > li:nth-child('+tattpos+')').css("color","white");
                $('ul.centralatt > li:nth-child('+tattpos+')').css("border-bottom","0");
                $('ul.centralatt > li:nth-child('+cattpos+')').css("color","white");
                $('ul.centralatt > li:nth-child('+cattpos+')').css("margin-top","-5px");
                $('ul.centralatt > li:nth-child('+cattpos+')').css("border-top","0");
                $('ul.centralatt > li:nth-child('+cattpos+')').css("min-height","20px");
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
            newcurrentarray = $(this).sortable('toArray');
            var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
            var size = $(".newcreate li").size();
            if(cpdbattpos == 0 && size>1) {
                alert(addElementBelowText);
                $(ui.sender).sortable('cancel');
            } else if(newcurrentarray[cpdbattpos+1]=='tb') {
                alert(onlyOneAttributeMappedText);
                $(ui.sender).sortable('cancel');
            } else {
                $('.newcreate').append('<li id="tb_'+$(ui.item).attr('id')+'"><input type="text" id="td_'+$(ui.item).attr('id')+'" value=\"'+$(ui.item).attr('name')+'\"></li>');
                $(ui.item).html($(ui.item).attr('id').replace('t_',''));
                cpdbattpos = cpdbattpos+1;
                $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("color", "white");
                $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("background-color","#696565");
                $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("border-bottom","0");
                $('li#tb_'+$(ui.item).attr('id')).css("background-color", "#696565");
                $('li#tb_'+$(ui.item).attr('id')).css("margin-top", "-5px");
                $('li#tb_'+$(ui.item).attr('id')).css("border-top", "0");
            }
        }
    });
    $("#overwrite").click(function(){
        if($("#overwrite").is(':checked')) {attoverwrite=true;} else {attoverwrite=false;}
    });
    $("#overwriteman").click(function(){
        if($("#overwriteman").is(':checked')) {attoverwriteman=true;} else {attoverwriteman=false;}
    });
    $("#createautomap").click(function(){
        if($("#createautomap").is(':checked')) {attcreateautomap=true;} else {attcreateautomap=false;}
    });
    $('#attmap').click(function(){
        var mappedarray = {};
        var anewcurrentarray = {};
        $.each(tokencurrentarray, function(index,value) {
            if(value[0]=='t') {
                mappedarray[encodeURI(tokencurrentarray[index-1].substring(2))] = value.substring(2);
            }
        });
        $.each(newcurrentarray, function(index,value) {
            if(value[0]=='t') {
                anewcurrentarray[value.substring(2)] = $("#td_"+value).val();
            }
        });
        /* $("#processing").dialog({
	        height: 90,
	        width: 50,
	        modal: true
        }); */

        $("#processing").load(copyUrl, {
            mapped: mappedarray,
            newarr: anewcurrentarray,
            surveyid: surveyId,
            overwriteauto: attoverwrite,
            overwriteman: attoverwriteman,
            createautomap: attcreateautomap
        }, function(msg){
            alert(msg);
            $(this).dialog("close");
            $(location).attr('href',redUrl);
        });
    });
});