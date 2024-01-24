
// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:scriptcomplete', function(){

    var windowHeight = $(window).height();
    var tokencurrentarray = {};
    var newcurrentarray = {};

    var centralattribute = $('#centralattribute');
    var newcreated = $('#newcreated');
    var tokenattribute = $('#tokenattribute');

    if($("#overwrite").is(':checked')) {var attoverwrite=true;} else {var attoverwrite=false;}
    if($("#overwriteman").is(':checked')) {var attoverwriteman=true;} else {var attoverwriteman=false;}
    if($("#createautomap").is(':checked')) {var attcreateautomap=true;} else {var attcreateautomap=false;}
    if($("#overwritest").is(':checked')) {var attoverwritest=true;} else {var attoverwritest=false;}

    centralattribute.css({'min-height': windowHeight * 0.5});
    newcreated.css({'min-height': windowHeight * 0.5});
    tokenattribute.css({'min-height': windowHeight * 0.5});
    var ncHeadingHeight = $('#newcreated .card-header').outerHeight();
    $('.newcreate').css({
        'padding-bottom': 0,
        'min-height': (windowHeight * 0.5) - ncHeadingHeight - 30
    });
    $('#cpdbatt').css({
        'padding-bottom': 0,
        'min-height': (windowHeight * 0.5) - ncHeadingHeight - 30
    });

    $("#overwrite").click(function (){
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
        connectWith:'.tokenatt-container, .newcreate, .standardfields',
        helper: 'clone',
        appendTo: 'body',
        receive: function(event,ui) {
            newcurrentarray = $(this).sortable('toArray');
            var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
            cpdbattpos = cpdbattpos+1;
            $('#cpdbatt > :nth-child('+cpdbattpos+')').css("color", "black");
            $('#cpdbatt > :nth-child('+cpdbattpos+')').css("background-color","white");
        }
    });
    $(".standardfields").sortable({
        helper: 'clone',
        appendTo: 'body',
        connectWith: 'div',
        beforeStop: function(event,ui) {
            $(this).sortable('cancel');
        },
        receive: function(event, ui) {
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
                // Change colors?
            }
        }
    });
    $(".tokenatt-container").sortable({
        cancel: '.ui-state-disabled',
        helper: 'clone',
        appendTo: 'body',
        connectWith: 'div',
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
                // Change CSS
                $(ui.item).removeClass('col-12');
                $(ui.item).addClass('cpdb-attribute');
                $(ui.item).wrap('<div class="col-md-6"></div>');

                // Insert nice arrows
                //var t = $(ui.item).parent('.tokenatt-container');  // Does not work.
                var t = $(ui.item).parent('div').parent('div');  // TODO: Bad, should not rely on DOM structure
                t = t.find('.token-attribute .card-body');
                t.append('<span class="ri-arrow-left-right-fill tokenatt-arrow"></span>');
            }
        }
    });
    $(".newcreate").sortable({
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
        },
        remove: function(event, ui) {
            newcurrentarray = $(this).sortable('toArray');
        }
    });

    $('#attmap').click(function() {

        // Iterate all containers in mapped attributes
        var mappedarray = {};
        $('.tokenatt-container').each(function (index, value) {
            var tokenAttributeId = $(value).find('.token-attribute').attr('id');
            var cpdbAttributeId = $(value).find('.cpdb-attribute').attr('id');

            var bothAreDefined = tokenAttributeId !== undefined && cpdbAttributeId !== undefined;
            if (bothAreDefined) {
                mappedarray[tokenAttributeId.substring(2)] = cpdbAttributeId.substring(2);
            }
        });

        newcurrentarray = {};
        $('.newcreate .card').each(function(index, value) {
            var id = $(value).attr('id').substring(2);
            newcurrentarray[index] = id;
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
                $('#attribute-map-participant-modal .modal-body').html(msg);
                $('#attribute-map-participant-modal').on('hide.bs.modal' , function (e) {
                    $(location).attr('href',redUrl);
                });
                const modal = new bootstrap.Modal(document.getElementById('attribute-map-participant-modal'), {});
                modal.show();
        });
    });

    $('.tokenatt .card-default .tokenAttributeId').disableSelection();
});
