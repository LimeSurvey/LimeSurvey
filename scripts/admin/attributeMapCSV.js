$(document).ready(function() {
    if(!$('#csvattribute').length ) {
        //alert("All the attributes are automatically mapped");
    }
    var height = $(document).height();
    var width = $(document).width();
    
    var headingHeight = 0;
    $('.attribute-column .heading').each(function(i) {
        if($(this).height() > headingHeight) {
            headingHeight = $(this).height();
        }
    });
    $('.attribute-column .heading').height(headingHeight);
    
    function adjustHeights() {
        $('.attribute-column, .droppable').css({ 'height': 'auto' });
        $('.attribute-column').height($('.draggable-container').height());
        
        var ncHeadingHeight = $('#newcreated .heading').outerHeight();
        var ncInstructionsHeight = $('#newcreated .instructions').outerHeight();
        $('.newcreate').css({
            'height':$('#newcreated').height()-ncHeadingHeight-5-ncInstructionsHeight
        });
        var csvHeadingHeight = $('#csvattribute .heading').outerHeight();
        var csvInstructionsHeight = $('#csvattribute .instructions').outerHeight();
        $('.csvatt').css({
            'height':$('#csvattribute').height()-csvHeadingHeight-5-csvInstructionsHeight
        });
    }
    
    adjustHeights();    
    
    if($("#overwrite").is(':checked')) {
        var attoverwrite=true;
    } 
    else {
        var attoverwrite=false;
    }
                
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
                $(newDraggable).text($(newDraggable).attr('data-name'));
            }        
            // Dropped in new attributes
            if($(this).hasClass('newcreate')) { 
                $(newDraggable).html($(newDraggable).attr('id').replace('cs_',''));
                $(newDraggable).prepend('<input type="text" id="td_'+$(newDraggable).attr('id')+'" value=\"'+$(newDraggable).attr('data-name')+'\">');
            }  
                        
            // Reset the mappable attribute classes 
            $('.mappable-attribute-wrapper').removeClass('paired');
            $('.mappable-attribute-wrapper .csv-attribute').closest('.mappable-attribute-wrapper').addClass('paired');
            $('.mappable-attribute-wrapper').droppable('enable');
            $('.mappable-attribute-wrapper.paired').droppable('disable');
            
            adjustHeights();
        } 
    });
    

    $('#attmapcancel').click(function(){
        $.post(mapCSVcancelled, {fullfilepath : thefilepath},
        function(data){
            $(location).attr('href',displayParticipants);
        });
    });
    
    $("#overwrite").click(function(){
        if($("#overwrite").is(':checked')) {
            attoverwrite=true;
        } 
        else {
            attoverwrite=false;
        }
    });

    $('#attmap').click(function(){
        var anewcurrentarray = {};
        newcurrentarray = new Array();
        $('#newcreated .attribute-item').each(function(i) {
            newcurrentarray.push($(this).attr('id'));
        });
        $.each(newcurrentarray, function(index,value) {
			if(value[0]=='c') {
                anewcurrentarray[value.substring(3)] = $("#td_"+value).val();
            }
        });
        
        var mappedarray = {};
        cpdbattarray = new Array();
        $('#centralattribute .attribute-item').each(function(i) {
            cpdbattarray.push($(this).attr('id'));
        });
        $.each(cpdbattarray, function(index,value) {
            if(value[0]=='c' && value[1]=='s') {
                mappedarray[cpdbattarray[index-1].substring(2)] = value.substring(3);
            }
        });

        var dialog_buttons={};

        dialog_buttons[okBtn]=function(){
            $(location).attr('href',displayParticipants);
        };

        $("#processing").dialog({
            height: 550,
            width: 700,
            modal: true,
            buttons: dialog_buttons,
            open: function(event, ui) {
                $('#processing').parent().find("button").each(function() {
                    if ($(this).text() == okBtn) {
                        $(this).attr('disabled', true);
                    }
                });
            }
        });

        $("#processing").load(copyUrl, {
            characterset: characterset,
            separatorused : separator,
            fullfilepath : thefilepath,
            newarray : anewcurrentarray,
            mappedarray : mappedarray,
            overwrite : attoverwrite,
            filterbea : filterblankemails
        }, function(msg){
            $('#processing').parent().find("button").each(function() {
                if ($(this).text() == okBtn) {
                    $(this).attr('disabled', false);
                }
            });
        });
    });
});