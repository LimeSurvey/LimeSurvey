var onDocumentReadyAttributeMapToken = function(){
    var height = $(document).height();
    var width = $(document).width();
    var tokencurrentarray = {};
    if($("#overwrite").is(':checked')) {var attoverwrite=true;} else {var attoverwrite=false;}
    if($("#overwriteman").is(':checked')) {var attoverwriteman=true;} else {var attoverwriteman=false;}
    if($("#createautomap").is(':checked')) {var attcreateautomap=true;} else {var attcreateautomap=false;}
    
    var headingHeight = 0;
    $('.attribute-column .panel-heading').each(function(i) {
        if($(this).height() > headingHeight) {
            headingHeight = $(this).height();
        }
    });
    $('.attribute-column .panel-heading').height(headingHeight);
    
    function adjustHeights() {
        $('.attribute-column, .droppable').css({ 'height': 'auto' });
        $('.attribute-column').height($('.draggable-container').height());
        
        var ncHeadingHeight = $('#newcreated .panel-heading').outerHeight();
        $('.newcreate').css({
            'height':$('#newcreated').height()-ncHeadingHeight-5
        });
        var taHeadingHeight = $('#tokenattribute .panel-heading').outerHeight();
        $('#tokenatt').css({
            'height':$('#tokenattribute').height()-taHeadingHeight-5
        });
    }
    
    adjustHeights();
                
    // Make the items draggable
    $('.draggable').draggable({ 
        revert: "invalid",
        appendTo: "body",
        containment: $('.draggable-container'),
        zindex: 150,
        opacity: 0.75
    });
            
    // Set the targets for the draggables
    $('.droppable').droppable({ 
        hoverClass: 'target-hover', 
        accept: '.draggable',
        over: function(event, ui) {
            adjustHeights();
        },
        drop: function(event, ui) {
                
            // Physically  move the draggable to the target (the plugin just visually moves it)
            // Need to use a clone for this to fake out iPad
            var newDraggable = $(ui.draggable).clone();
            $(newDraggable).appendTo(this);
            $(ui.draggable).remove();
            
            // Clean up the new clone
            $(newDraggable).removeClass('ui-draggable-dragging').css({
                'left':'0',
                'z-index': '',
                'opacity': 1
            }).animate({
                top: ''
            }, 300).draggable({ 
                revert: "invalid",
                appendTo: "body",
                containment: $('.draggable-container'),
                opacity: 0.75
            });
                        
            // Remove the text input if dropped out of the new attributes column
            if(!$(this).hasClass('newcreate') && $('input[type="text"]', newDraggable).length > 0) { 
                $('input[type="text"]', newDraggable).remove();
                $(newDraggable).html('<div class="panel-body">' + $(newDraggable).attr('data-name') + "</div>");
            }        

            // Dropped in new attributes
            if($(this).hasClass('newcreate')) { 
                $(newDraggable).html($(newDraggable).attr('id').replace('t_',''));
                $(newDraggable).prepend('<input type="text" id="td_'+$(newDraggable).attr('id')+'" value=\"'+$(newDraggable).attr('data-name')+'\">');
            }            

            // Reset the mappable attribute classes        
            $('.mappable-attribute-wrapper').removeClass('paired');
            $('.mappable-attribute-wrapper .token-attribute').closest('.mappable-attribute-wrapper').addClass('paired');
            $('.mappable-attribute-wrapper').droppable('enable');
            $('.mappable-attribute-wrapper.paired').droppable('disable');
            
            adjustHeights();
        } 
    });
    
    $("#back").click(function(){
        var backURL = document.URL.replace(/participants\/sa\/attributeMapToken\/sid\//, 'tokens/sa/browse/surveyid/');
        window.location = backURL;
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

    // Continue button
    $('#attmap').click(function(){

        var anewcurrentarray = {};
        newcurrentarray = new Array();

        $('#newcreated .attribute-item').each(function(i) {
            console.ls.log(this);
            newcurrentarray.push($(this).attr('id'));
        });

        $.each(newcurrentarray, function(index,value) {
            console.ls.log(value);
            if(value[0]=='t') {
                anewcurrentarray[value.substring(2)] = $("#td_"+value).val();
            }
        });

        var mappedarray = {};
        tokencurrentarray = new Array();
        $('#centralattribute .attribute-item').each(function(i) {
            tokencurrentarray.push($(this).attr('id'));
        });

        $.each(tokencurrentarray, function(index,value) {
            if(value[0]=='t') {
                mappedarray[encodeURI(tokencurrentarray[index-1].substring(2))] = value.substring(2);
            }
        });

        console.ls.log('mappedarray', mappedarray);
        console.ls.log('anewcurrentarray', anewcurrentarray);
        console.ls.log(attoverwrite);
        console.ls.log(attoverwriteman);
        console.ls.log(attcreateautomap);

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
            $('#attribute-map-token-modal .modal-body').html(msg);
            $('#attribute-map-token-modal').on('hide.bs.modal' , function (e) {
                $(location).attr('href',redUrl);
            });
            $('#attribute-map-token-modal').modal();

        });
    });

};

$(document).on('ready  pjax:scriptcomplete', onDocumentReadyAttributeMapToken);
$(document).on(' pjax:scriptcomplete',onDocumentReadyAttributeMapToken);