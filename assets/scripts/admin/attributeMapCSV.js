
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

    /**
     * Turn a CSV field into an attribute to be created: An input for the attribute name,
     * labelled with the CSV field name, and a button to move it back to the CSV field names
     */
    function makeNewAttribute($elem) {
        var id = $elem.attr('id').replace(/ /g, '');
        var name = $elem.attr('data-name');
        var $input = $('<input class="form-control" type="text">')
            .attr({id: 'td_' + id, 'aria-label': name})
            .val(name);
        // A replacement function, so that "$&" etc. in the field name are not replacement patterns
        var moveBackLabel = moveBackTxt.replace('%s', function () { return name; });
        var $moveBack = $('<button type="button" class="btn btn-outline-secondary btn-sm ms-2 action-move-back"><i class="ri-arrow-go-back-fill" aria-hidden="true"></i></button>')
            .attr('title', moveBackLabel);
        var $body = $('<div class="card-body d-flex align-items-center"></div>')
            .append($input)
            .append($('<span class="ms-2"></span>').text(name))
            .append($moveBack);
        $elem.empty().append($body);
    }

    /**
     * Turn an attribute to be created back into a plain CSV field
     */
    function makeCsvField($elem) {
        $elem.empty().append($('<div class="card-body"></div>').text($elem.attr('data-name')));
    }

    // Move an attribute to be created back to the CSV field names
    $(document).off('click.attributeMapCSV').on('click.attributeMapCSV', '.newcreate .action-move-back', function () {
        var index = $('.newcreate .action-move-back').index(this);
        var $elem = $(this).closest('.csv-attribute-item');
        makeCsvField($elem);
        $elem.detach().appendTo('.droppable-csv');
        adjustHeights();
        // Keep the keyboard focus on the page: On the next "move back" button (or the last one), else on "Move all"
        var $buttons = $('.newcreate .action-move-back');
        var $next = $buttons.eq(Math.min(index, $buttons.length - 1));
        ($buttons.length ? $next : $('#move-all')).trigger('focus');
    });

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
                makeCsvField(newDraggable);
            }        

            // Dropped in new attributes, unless it was already there (keeps the attribute name the user typed)
            if($(this).hasClass('newcreate') && $('input[type="text"]', newDraggable).length === 0) {
                makeNewAttribute(newDraggable);
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
            makeNewAttribute($elem);
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
