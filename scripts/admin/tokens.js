/**
 * jQuery Plugin to manage the date in token modal edition.
 * Some fields, like "Completed", can have string value (eg: 'N') or a date value.
 * They are displayed via a switch hidding or showing a date picker.
 */
$.fn.YesNoDate = function(options)
{
    var that            = $(this);                                              // calling element
    $(document).ready(function(){
        var $elSwitch        = that.find('.YesNoDateSwitch').first();           // switch element (generated with YiiWheels widgets)
        var $elDateContainer = that.find('.date-container').first();            // date time picker container (to show/hide)
        var $elDate          = that.find('.YesNoDatePicker').first();           // date time picker element (generated with YiiWheels widgets)
        var $elHiddenInput   = that.find('.YesNoDateHidden').first();           // input form, containing the value to submit to the database

        // The view is called without processing output (no javascript)
        // So we must apply js to widget elements
        $elSwitch.bootstrapSwitch();                                            // Generate the switch
        $elDate.datetimepicker({locale: that.data('locale')})                   // Generate the date time picker

        // When user switch
        $(document).on( 'switchChange.bootstrapSwitch', '#'+$elSwitch.attr('id'), function(event, state)
        {
            if (state==true)
            {
                // Show date
                $elDateContainer.show();
            }
            else
            {
                // Hide date, set hidden input to "N"
                $elDateContainer.hide();
                $elHiddenInput.attr('value', 'N');
            }
        });

        // When user change date
        $(document).on('dp.change', '#'+$elDate.attr('id')+'_datetimepicker', function(e){
            $elHiddenInput.attr('value', e.date.format('YYYY-MM-DD HH:MM'));
        })
    });
}

$.fn.YesNo = function(options)
{
    var that              = $(this);                                            // calling element
    var $elHiddenInput   = that.find('.YesNoDateHidden').first();           // input form, containing the value to submit to the database


    $(document).ready(function(){
        var $elSwitch        = that.find('.YesNoSwitch').first();               // switch element (generated with YiiWheels widgets)
        $elSwitch.bootstrapSwitch();                                            // Generate the switch

        // When user change date
        $(document).on( 'switchChange.bootstrapSwitch', '#'+$elSwitch.attr('id'), function(event, state)
        {
            if (state==true)
            {
                $elHiddenInput.attr('value', 'Y');
            }
            else
            {
                $elHiddenInput.attr('value', 'N');
            }


        })

    });
}

/**
 * Provide to this function a element containing form-groups,
 * it will stick the text labels on its border
 */
$.fn.stickLabelOnLeft  = function(options)
{
    var that = $(this);
    var formgroups = that.find('.form-group');
    $maxWidth  = 0;
    $elWidestLeftLabel = '';
    formgroups.each( function() {
        var elLeftLabel = $(this).find('label').first();
        $LeftLabelWidth = elLeftLabel.textWidth();

        if ($LeftLabelWidth > $maxWidth )
        {
            $maxWidth =$LeftLabelWidth;
            $elWidestLeftLabel = elLeftLabel;
        }
    });

    $distanceFromBorder = ( $maxWidth - $elWidestLeftLabel.width());
    if ( $distanceFromBorder < 0)
    {
        that.css({
            position: "relative",
            left: $distanceFromBorder,
        });
    }

}

// Calculate width of text from DOM element or string. By Phil Freo <http://philfreo.com>
$.fn.textWidth = function(text, font) {
    if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
    $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
    return $.fn.textWidth.fakeEl.width();
};


function submitEditToken(){
    $form       = $('#edittoken');
    $datas      = $form.serialize();
    $actionUrl  = $form.attr('action');
    $gridid     = $('.listActions').data('grid-id');
    $modal      = $('#editTokenModal');

    $ajaxLoader = $('#ajaxContainerLoading2');
    $('#modal-content').empty();
    $ajaxLoader.show();                                         // Show the ajax loader
    // Ajax request
    $.ajax({
        url  : $actionUrl,
        type : 'POST',
        data : $datas,

        // html contains the buttons
        success : function(html, statut){
            $ajaxLoader.hide();
            //Using Try/Catch here to catch errors if there is no grid
            
            try{
                $.fn.yiiGridView.update('token-grid', {
                    complete: function(s){
                        $modal.modal('hide');
                    } // Update the surveys list
                });                   
            } catch(e){
                if(e){console.log(e); $modal.modal('hide');}
            }finally{
                $ajaxLoader.hide();
            }
        },
        error :  function(html, statut){
            $ajaxLoader.hide();
            $('#modal-content').empty().append(html);
            console.log(html);
        }
    });
}

/**
 * Scroll the pager and the footer when scrolling horizontally
 */
$(document).ready(function(){

    if($('#sent-yes-no-date-container').length > 0)
    {
        $('#general').stickLabelOnLeft();

        $('.yes-no-date-container').each(function(el){
            $(this).YesNoDate();
        });

        $('.yes-no-container').each(function(el){
            $(this).YesNo();
        });

        $('#validfrom').datetimepicker({locale: $('#validfrom').data('locale')});
        $('#validuntil').datetimepicker({locale: $('#validuntil').data('locale')});

        $('.date .input-group-addon').on('click', function(){
            $prev = $(this).siblings();
            $prev.data("DateTimePicker").show();
        });
    }

    $('.scrolling-wrapper').scroll(function(){
        $('#tokenListPager').css({
            'left': $(this).scrollLeft() ,
        });
    });

    /**
     * Token delete Token
     */
    $(document).on('click', '.delete-token', function(){
        var $that       = $(this),
            actionUrl  = $that.data('url'),
            $modal      = $('#confirmation-modal');
        
        $modal.data('ajax-url', actionUrl);
        $modal.data('href', "#");
        $modal.modal('show');   
        $modal.find('.modal-footer-yes-no').find('a.btn-ok').on('click', function(click){
            $.ajax({
                url: actionUrl,
                method: "GET",
                success: function(data){
                    try{
                        $.fn.yiiGridView.update('token-grid', {
                            complete: function(s){
                                $modal.modal('hide');
                            } // Update the surveys list
                        });                   
                    } catch(e){
                        if(e){console.log(e); $modal.modal('hide');}
                    }
                }
            });
        })
    });
    /**
     * Token edition
     */
    $(document).on( 'click', '.edit-token', function(){
        var $that       = $(this),
            $sid        = $that.data('sid'),
            $tid        = $that.data('tid'),
            $actionUrl  = $that.data('url'),
            $modal      = $('#editTokenModal'),
            $modalBody  = $modal.find('.modal-body'),
            $ajaxLoader = $('#ajaxContainerLoading2'),
            $oldModalBody   = $modalBody.html();


        $ajaxLoader.show();
        $modal.modal('show');
        // Ajax request
        $.ajax({
            url : $actionUrl,
            type : 'GET',

            // html contains the buttons
            success : function(html, statut){

                $('#modal-content').empty().append(html);                       // Inject the returned HTML in the modal body

                // Apply the yes/no/date jquery plugin to the elements loaded via ajax
                /*
                    $('#sent-yes-no-date-container').YesNoDate();
                    $('#remind-yes-no-date-container').YesNoDate();
                    $('#completed-yes-no-date-container').YesNoDate();
                */

                $('.yes-no-date-container').each(function(el){
                    $(this).YesNoDate();
                });


                $('.yes-no-container').each(function(el){
                    $(this).YesNo();
                });

                $('#validfrom').datetimepicker({locale: $('#validfrom').data('locale')});
                $('#validuntil').datetimepicker({locale: $('#validuntil').data('locale')});

                $('.date .input-group-addon').on('click', function(){
                    $prev = $(this).siblings();
                    $prev.data("DateTimePicker").show();
                });

                var elGeneral  = $('#general');

                // Fake hide of modal content, so we can still get width of inner elements like labels
                var previousCss  = $("#modal-content").attr("style");
                $("#modal-content")
                    .css({
                        position:   'absolute', // Optional if #myDiv is already absolute
                        visibility: 'hidden',
                        display:    'block'
                    });

                // Stick the labels on the left side
                // Sometime, the content is loaded after modal is shown, sometimes not. So, we wait 200ms just in case (For label width)
                setTimeout(function(){
                    elGeneral.stickLabelOnLeft();
                    $ajaxLoader.hide();
                    // Remove fake hide
                    $("#modal-content").attr("style", previousCss ? previousCss : "");
                }, 200);

            },
            error :  function(html, statut){
                $ajaxLoader.hide();
                $('#modal-content').empty().append(html);
                console.log(html);
            }
        });
    });


    $(document).on('submit','#edittoken',function(){
        if($('#editTokenModal').length > 0 ){
            event.preventDefault();
            submitEditToken();
        }
    });

    /**
     * Save token
     */
    $("#save-edittoken").click(function(){
        submitEditToken();
    });


    $('#startbounceprocessing').click(function(){

        $that               = $(this);
        $url                = $that.data('url');
        $modal              = $('#tokenBounceModal');
        $ajaxLoader         = $('#ajaxContainerLoading');
        $modalBodyText      = $modal.find('.modal-body-text');

        $modalBodyText.empty();
        $ajaxLoader.show();
        $modal.modal();

        $.ajax({
            url: $url,
            type: 'get',
            success: function(html) {
                $ajaxLoader.hide();
                $modalBodyText.append(html);
            },
            error :  function(html, statut){
                $ajaxLoader.hide();
                $modalBodyText.append(html);
                console.log(html);
            },

        });
    });
});

var conditionid=1;
function checkbounces() {
    $("#dialog-modal").dialog('open');
    $('#dialog-modal').html('<p><img style="margin-top:42px" src="'+imageurl+'ajax-loader.gif" /></p>');
    $('#dialog-modal').load(sBounceProcessingURL);
}

function addcondition()
{
    // Seems unused
    conditionid++;
    html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td><select name='join_"+conditionid+"' id='join_"+conditionid+"'>\n\
    <option value='and'>"+andTxt+"</option><option value='or'>"+orTxt+"</option></td></tr>";
    html2 = "<tr><td><select name='field_"+conditionid+"' \n\ id='field_"+conditionid+"'>";
    for(col in colInformation){
        if(colInformation[col]['search'])
            html2 += "<option value='"+col+"'>"+colInformation[col]['description']+"</option>";
    }
    html2 += "</select></td><td>\n\
    <select name='condition_"+conditionid+"' id='condition_"+conditionid+"'><option value='equal'>"+searchtypes[0]+"</option><option value='contains'>"+searchtypes[1]+"</option>\n\
    <option value='notequal'>"+searchtypes[2]+"</option><option value='notcontains'>"+searchtypes[3]+"</option><option value='greaterthan'>"+searchtypes[4]+"</option>\n\
    <option value='lessthan'>"+searchtypes[5]+"</option></select></td>\n\<td><input type='text' id='conditiontext_"+conditionid+"' style='margin-left:10px;' /></td>\n\
    <td><img src="+minusbutton+" onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='removebutton'"+conditionid+">\n\
    <img src="+addbutton+" class='addcondition-button' style='margin-bottom:4px'></td></tr>";
    //$('#searchtable > tbody > tr').eq(id).after(html);
    $('#searchtable > tbody > tr').eq(conditionid).after(html);
    conditionid++;
    $('#searchtable > tbody > tr').eq(conditionid).after(html2);
    //idexternal++;
}


function addSelectedParticipantsToCPDB()
{
    var dialog_buttons={};
    var token = [];

    var token = jQuery('#displaytokens').jqGrid('getGridParam','selarrrow');

    if(token.length==0)
    {        /* build an array containing the various button functions */
        /* Needed because it's the only way to label a button with a variable */

        dialog_buttons[okBtn]=function(){
            $( this ).dialog( "close" );
        };
        /* End of building array for button functions */
        $('#norowselected').dialog({
            modal: true,
            buttons: dialog_buttons
        });
    }
    else
    {
        $("#addcpdb").load(postUrl, {
            participantid:token},function(){
                $(location).attr('href',attMapUrl+'/'+survey_id);
        });
    }

    /*$(":checked").each(function() {
    token.push($(this).attr('name'));
    });*/
}


$(document).ready(function() {

    // Code for AJAX download
    jQuery.download = function(url, data, method){
        //url and data options required
        if( url && data ){
            //data can be string of parameters or array/object
            data = typeof data == 'string' ? data : jQuery.param(data);
            //split params into form inputs
            var inputs = '';
            jQuery.each(data.split('&'), function(){
                var pair = this.split('=');
                inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />';
            });
            //send request
            jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
            .appendTo('body').submit().remove();
        };
    };
    // Code for AJAX download
    $(document).on("click",".addcondition-button",function(){
        conditionid++;
        html = "<tr name='joincondition_"+conditionid+"' id='joincondition_"+conditionid+"'><td><select class='form-control' name='join_"+conditionid+"' id='join_"+conditionid+"'><option value='and'>"+andTxt+"</option><option value='or'>"+orTxt+"</option></td><td></td></tr><tr><td><select class='form-control' name='field_"+conditionid+"' id='field_"+conditionid+"'>\n";
        for(col in colInformation){
            if(colInformation[col]['search'])
                html += "<option value='"+col+"'>"+colInformation[col]['description']+"</option>";
        }
        html += "</select>\n\</td>\n\<td>\n\
        <select class='form-control' name='condition_"+conditionid+"' id='condition_"+conditionid+"'>\n\
        <option value='equal'>"+searchtypes[0]+"</option>\n\
        <option value='contains'>"+searchtypes[1]+"</option>\n\
        <option value='notequal'>"+searchtypes[2]+"</option>\n\
        <option value='notcontains'>"+searchtypes[3]+"</option>\n\
        <option value='greaterthan'>"+searchtypes[4]+"</option>\n\
        <option value='lessthan'>"+searchtypes[5]+"</option>\n\
        </select></td>\n\<td><input class='form-control' type='text' id='conditiontext_"+conditionid+"' /></td>\n\
        <td><span data-toggle='tooltip' title='" + sDelete + "' class='ui-pg-button glyphicon glyphicon-trash text-danger' onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='ui-icon removebutton'"+conditionid+"></span>\n\
        <span data-toggle='tooltip' title='" + sAdd + "' class='ui-pg-button addcondition-button ui-icon text-success icon-add' style='margin-bottom:4px'></span></td></tr><tr></tr>";
        $('#searchtable tr:last').after(html);
        $('[data-toggle="tooltip"]').tooltip()
    });
    if(typeof searchconditions === "undefined") {
        searchconditions = {};
    }
    var field;
    $('#searchbutton').click(function(){

    });


});

function centerInfoDialog() {
    var infoDialog = $("#info_dialog");
    var dialogparent = infoDialog.parent();
    infoDialog.css({ 'left': Math.round((dialogparent.width() - infoDialog.width()) / 2)+'px' });
}

function updatePageAfterGrid(){
    var oGrid=$("#displaytokens");
    var iLastPage=parseInt(oGrid.jqGrid('getGridParam', 'lastpage'));
    var iPage=parseInt(oGrid.jqGrid('getGridParam', 'page'));
    if(iPage>1)
    {
        iPrevPage=iPage-1;
        $(".databegin").click(function(){
            oGrid.setGridParam({page:1}).trigger("reloadGrid");
        });
        $(".gridcontrol.databegin").removeClass("disabled");
        $(".databack").click(function(){
            oGrid.setGridParam({page:iPrevPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.databack").removeClass("disabled");
    }
    else
    {
        $(".databegin").click(function(){});
        $(".gridcontrol.databegin").addClass("disabled");
        $(".databack").click(function(){});
        $(".gridcontrol.databack").addClass("disabled");
    }
    if(iPage<iLastPage)
    {
        iNextPage=iPage+1;
        $(".dataend").click(function(){
            oGrid.setGridParam({page:iLastPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.dataend").removeClass("disabled");
        $(".dataforward").click(function(){
            oGrid.setGridParam({page:iNextPage}).trigger("reloadGrid");
        });
        $(".gridcontrol.dataforward").removeClass("disabled");
    }
    else
    {
        $(".dataend").click(function(){});
        $(".gridcontrol.dataend").addClass("disabled");
        $(".dataforward").click(function(){});
        $(".gridcontrol.dataforward").addClass("disabled");
    }

}
