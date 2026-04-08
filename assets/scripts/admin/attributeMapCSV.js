
// Namespace
var LS = LS || {  onDocumentReady: {} };


$(document).on('ready pjax:scriptcomplete', function() {

    if(!$('#csvattribute').length ) {
        //alert("All the attributes are automatically mapped");
    }
    
    // Find the biggest column and set both to that height
    // TODO: Not needed since BS5 can adjust height.
    function adjustHeights() {
        var max = Math.max($('.droppable-new').height(), $('.droppable-csv').height());
        console.log('max', max);

        $('.droppable-new').css('min-height', max);
        $('.droppable-csv').css('min-height', max);
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
        revert: 'invalid',
        appendTo: 'body',
        zIndex: 150,
        containment: $('.draggable-container'),
        opacity: 0.75
    });
            
    // Set the targets for the draggables
    // Droppable into first and second column
    $('.droppable-csv, .droppable-new').droppable({ 
        hoverClass: 'target-hover', 
        accept: '.draggable',
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
                'opacity': 1,
                'width': ''
            }).animate({
                top: ''
            }, 300).draggable({ 
                revert: "invalid",
                zIndex: 150,
                appendTo: "body",
                containment: $('.draggable-container'),
                opacity: 0.75
            });
                        
            // Remove the text input if dropped out of the new attributes column
            if(!$(this).hasClass('newcreate') && $('input[type="text"]', newDraggable).length > 0) { 
                $('input[type="text"]', newDraggable).remove();
                $(newDraggable).html('<div class="card-body">' + $(newDraggable).attr('data-name') + '</div>');
            }        

            // Dropped in new attributes
            if($(this).hasClass('newcreate')) { 
                newDraggable.html('<div class="card-body">' + newDraggable.attr('id').replace('cs_','') + '</div>');
                var id = newDraggable.attr('id').replace(/ /g, '');
                var name = newDraggable.attr('data-name');
                newDraggable.find($('.card-body')).prepend('<input class="form-control" type="text" id="td_' + id + '" value="' + name + '">&nbsp;');
            }

            // Reset the mappable attribute classes 
            $('.mappable-attribute-wrapper').removeClass('paired');
            $('.mappable-attribute-wrapper .csv-attribute').closest('.mappable-attribute-wrapper').addClass('paired');
            $('.mappable-attribute-wrapper').droppable('enable');
            $('.mappable-attribute-wrapper.paired').droppable('disable');
            
            adjustHeights();
        } 
    });

    // The area to map CSV attributes to existent participant attributes
    $('.droppable-map').droppable({
        hoverClass: 'target-hover', 
        accept: '.draggable',
        drop: function(event, ui) {

            // Insert nice arrow
            var col = $(this).find('.col-6:first-child');
            col.append('<span class="ri-arrow-left-right-fill csvatt-arrow"></span>');

            // Physically  move the draggable to the target (the plugin just visually moves it)
            // Need to use a clone for this to fake out iPad
            var newDraggable = $(ui.draggable).clone();
            newDraggable.css('width', '');
            newDraggable.css('overflow', 'hidden');
            newDraggable.css('white-space', 'nowrap');
            newDraggable.appendTo(this);

            var that = this;

            newDraggable.draggable({ 
                revert: "invalid",
                zIndex: 150,
                appendTo: "body",
                containment: $('.draggable-container'),
                opacity: 0.75,
                stop: function(event, ui) {
                    // ui.helper, ui.position, .ui.offset
                    console.ls.log(col);
                    col.find('.fa-arrows-h').remove();
                    col.next().remove();
                    $(that).droppable('enable');
                }
            });

            $(ui.draggable).remove();

            // Don't allow user to drop more attributes here
            $(this).droppable('disable');

            // Fix CSS
            newDraggable.removeClass('ui-draggable-dragging').css({
                'left': '0',
                'top': '0',
                'z-index': '',
                'opacity': 1
            });

            // Remove the text input if dropped out of the new attributes column
            if(!$(this).hasClass('newcreate') && $('input[type="text"]', newDraggable).length > 0) { 
                $('input[type="text"]', newDraggable).remove();
                $(newDraggable).text($(newDraggable).attr('data-name'));
            }        

            newDraggable.wrap('<div class="col-6"></div>');

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

    // Click Move all fields to created column (mid column)
    $('#move-all').click(function () {
        $('.droppable-csv .csv-attribute-item').each(function(i, elem) {
            var $elem = $(elem);
            $elem.html('<div class="card-body">' + $elem.attr('id').replace('cs_','') + '</div>');
            var id = $elem.attr('id').replace(/ /g, '');
            var name = $elem.attr('data-name');
            $elem.find($('.card-body'))
                .prepend('<input class="form-control" type="text" id="td_' + id + '" value="' + name + '">');
            $elem.detach().appendTo('.newcreate');
            adjustHeights();
        });
    });

    // Click Continue
    $('#attmap').click(function(){
        var anewcurrentarray = {};
        var newcurrentarray = [];
        $('#newcreated .csv-attribute-item').each(function(i) {
            newcurrentarray.push($(this).attr('id'));
        });
        $.each(newcurrentarray, function(index,value) {
			if(value[0]=='c') {
                var id = value.replace(/ /g, '');
                anewcurrentarray[value.substring(3)] = $("#td_" + id).val();
            }
        });
        
        var mappedarray = {};
        cpdbattarray = new Array();
        $('#centralattribute .csv-attribute-item').each(function(i) {
            cpdbattarray.push($(this).attr('id'));
        });

        $.each(cpdbattarray, function(index,value) {
            if(value[0]=='c' && value[1]=='s') {
                mappedarray[cpdbattarray[index-1].substring(2)] = value.substring(3);
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
            var options = {};
            var uploadSummaryModal = new bootstrap.Modal(document.getElementById('attribute-map-csv-modal'), options);
            uploadSummaryModal.show();
        });
    });
});
