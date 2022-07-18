
var LS = LS || {
    onDocumentReady: {}
};

/**
 * jQuery Plugin to manage the date in token modal edit.
 * Some fields, like "Completed", can have string value (eg: 'N') or a date value.
 * They are displayed via a switch hidding or showing a date picker.
 */
$.fn.YesNoDate = function(options)
{
    var that            = $(this);                                              // calling element
    that.onReadyMethod = function(){
        var $elSwitch        = that.find('.YesNoDateSwitch').first(),           // switch element (generated with YiiWheels widgets)
            $elDateContainer = that.find('.date-container').first(),            // date time picker container (to show/hide)
            $elDate          = that.find('.YesNoDatePicker').first(),           // date time picker element (generated with YiiWheels widgets)
            $elHiddenInput   = that.find('.YesNoDateHidden').first();           // input form, containing the value to submit to the database

        console.ls.log('tokenform', {
            $elSwitch : $elSwitch,
            $elDateContainer : $elDateContainer,
            $elDate : $elDate,
            $elHiddenInput : $elHiddenInput
        });

        // The view is called without processing output (no javascript)
        // So we must apply js to widget elements
        $elSwitch.bootstrapSwitch();                                            // Generate the switch
        $elDate.datetimepicker({locale: that.data('locale')});                  // Generate the date time picker

        console.ls.log('$elSwitch', $elSwitch);
        // When user switch
        $elSwitch.on('switchChange.bootstrapSwitch', function(event, state)
        {
            console.ls.log('$elSwitch', event, state);
            if (state==true)
            {
                // Show date
                $elDateContainer.show();
                // If there is a date currently set in the date picker, assign that to the hidden input.
                // Otherwise, use the current date.
                const currentDate = $elDate.data('DateTimePicker').date() ?? moment();
                $elHiddenInput.attr('value', currentDate.format($elDate.data('date-format')));
            }
            else
            {
                // Hide date, set hidden input to "N"
                $elDateContainer.hide();
                $elHiddenInput.val('N');
            }
        });

        // When user change date
        $elDate.on('dp.change', function(e){
            $elHiddenInput.val(e.date.format($elDate.data('date-format')));
        })
    };
    return that;
}

$.fn.YesNo = function(options)
{
    var that              = $(this);                                            // calling element
    var $elHiddenInput   = that.find('.YesNoDateHidden').first();           // input form, containing the value to submit to the database

    that.onReadyMethod = function(){
        var $elSwitch        = that.find('.YesNoSwitch').first();               // switch element (generated with YiiWheels widgets)
        $elSwitch.bootstrapSwitch();                                            // Generate the switch

        // When user change date
        $elSwitch.on( 'switchChange.bootstrapSwitch', function(event, state)
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

    };
    return that;
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

/**
 * Used when user clicks "Save" in token edit modal
 */
function submitEditToken(){
    var $form       = $('#edittoken');
    var $datas      = $form.serialize();
    var $actionUrl  = $form.attr('action');
    var $modal      = $('#editTokenModal');
    var $gridId     = '';

    if (!$form[0].reportValidity()) {
        return;
    }

    // check which grid id exists on the page, to be able to update grid successfully
    if ($('#token-grid').length > 0) {
        $gridId = 'token-grid';
    } else if ($('#responses-grid').length > 0) {
        $gridId = 'responses-grid';
    }

    // Ajax request
    $.ajax({
        url  : $actionUrl,
        type : 'POST',
        data : $datas,

        success : function(result, stat) {
            if (result.success) {
                $modal.modal('hide');
            } else {
                var errorMsg = result.error.message ? result.error.message : result.error;
                if (!errorMsg) errorMsg = "Unexpected error";
                showError(errorMsg);
                return;
            }

            // Using Try/Catch here to catch errors if there is no grid
            try {
                $.fn.yiiGridView.update($gridId, {
                    complete: function(s){
                        $modal.modal('hide');
                    } // Update the surveys list
                });
            }
            catch (e){
                if (e) {
                    console.ls.error(e);
                    $modal.modal('hide');
                }
            }
        },
        error :  function(html, statut){
            $('#modal-content').empty().append(html);
        }
    });
}

function showError(msg) {
    $('#edittoken-error-container .alert-content').html(msg);
    $('#edittoken-error-container').show();
}

/**
 * Validates that mandatory additional attributes are filled
 */
function validateAdditionalAttributes() {
    const validationErrorMsg = $('#edittoken').attr('data-validation-error');

    let valid = true;
    $('.mandatory-attribute').each(function () {
        let value = $(this).val();
        if (value === null || value === "") {
            valid = false;
            if (!$('#custom').is(':visible')) {
                $('.nav-tabs a[href="#custom"]').tab('show');
            }
            showError(validationErrorMsg);
            $(this).trigger('invalid');
            return false;
        }
    });
    return valid;
}

/**
 * Validates some form fields checking that at least one is not empty when creating a participant.
 * @returns {boolean} false if all of the checked fields are empty and the subaction is inserttoken.
 */
function validateNotEmptyTokenForm() {
    if ($('#edittoken').find('[name="subaction"]').val() != 'inserttoken') {
        return true;
    }
    var isFormEmpty = $('#email').val() == '' && $('#firstname').val() == '' && $('#lastname').val() == '';
    if (isFormEmpty) {
        $('#emptyTokenConfirmationModal').modal('show');
        return false;
    }
    return true;
}

/**
 * Scroll the pager and the footer when scrolling horizontally
 */
$(document).on('ready  pjax:scriptcomplete', function(){

    if($('#sent-yes-no-date-container').length > 0)
    {
        $('#general').stickLabelOnLeft();

        $('#validfrom').datetimepicker({locale: $('#validfrom').data('locale')});
        $('#validuntil').datetimepicker({locale: $('#validuntil').data('locale')});

        $('.date .input-group-addon').on('click', function(){
            $prev = $(this).siblings();
            $prev.data("DateTimePicker").show();
        });
    }

    var modal = $('#massive-actions-modal-edit-0');
    if (modal.length) {
        modal.on('shown.bs.modal', function () {
            $('.yes-no-date-container').each(function(i,el){
                $(this).YesNoDate().onReadyMethod();
            });

            $('.yes-no-container').each(function(i,el){
                $(this).YesNo().onReadyMethod();
            });
        });
    }

    $(document).on('actions-updated', function() {
        $('.yes-no-date-container').each(function(i,el){
            $(this).YesNoDate().onReadyMethod();
        });

        $('.yes-no-container').each(function(i,el){
            $(this).YesNo().onReadyMethod();
        });
    });

    var initialScrollValue = $('.scrolling-wrapper').scrollLeft();
    var useRtl = $('input[name="rtl"]').val() === '1';

    if (useRtl) {
        $('.scrolling-wrapper').scroll(function(){
            var scrollAmount = Math.abs($('.scrolling-wrapper').scrollLeft() - initialScrollValue);
            $('#tokenListPager').css({
                'right': scrollAmount
            });
        });
    }
    else {
        $('.scrolling-wrapper').scroll(function(){
            $('#tokenListPager').css({
                'left': $(this).scrollLeft() ,
            });
        });
    }

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
                    
                    $('#token-grid').yiiGridView('update',{
                        complete: function(s){
                            $modal.modal('hide');
                        } // Update the surveys list
                    });
                }
            });
        })
    });

    $(document).off('click.edittoken', '.edit-token').on('click.edittoken', '.edit-token', startEditToken);

    $(document).off('submit.edittoken', '#edittoken').on('submit.edittoken', '#edittoken', function(event, params){
        var eventParams = params || {};
        if($('#editTokenModal').length > 0 ){
            event.preventDefault();
            submitEditToken();
            return;
        }
        if (!validateAdditionalAttributes()) {
            event.preventDefault();
            return false;
        }
        if (!eventParams.confirm_empty_save && !validateNotEmptyTokenForm()) {
            return false;
        }
    });

    /**
     * Save token
     */
    $("#save-edittoken").off('click.token-save').on('click.token-save', function() {
        if (validateAdditionalAttributes()) {
            submitEditToken();
        }
    });

    /**
     * Confirm save empty token
     */
    $("#save-empty-token").off('click.token-save').on('click.token-save', function() {
        $('#edittoken').trigger('submit', {confirm_empty_save: true});
    });


    $('#startbounceprocessing').click(function(){

        var $that               = $(this);
        var $url                = $that.data('url');
        var $modal              = $('#tokenBounceModal');
        var $ajaxLoader         = $('#ajaxContainerLoading');
        var $modalBodyText      = $modal.find('.modal-body-text');
        var $limebutton         = $modal.find('.modal-footer .limebutton');

        $modalBodyText.empty();
        $limebutton.empty().append('close');
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
                console.ls.error(html);
            },

        });
    });


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
        <td><span data-toggle='tooltip' title='" + sDelete + "' class='ui-pg-button fa fa-trash text-danger' onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='ui-icon removebutton'"+conditionid+"></span>\n\
        <span data-toggle='tooltip' title='" + sAdd + "' class='ui-pg-button addcondition-button ui-icon text-success icon-add' style='margin-bottom:4px'></span></td></tr><tr></tr>";
        $('#searchtable tr:last').after(html);
        window.LS.doToolTip();
    });
    if(typeof searchconditions === "undefined") {
        searchconditions = {};
    }
    var field;
    $('#searchbutton').click(function(){

    });
});

/**
 * Token edit
 */
var startEditToken = function(){
    var $that       = $(this),
        $sid        = $that.data('sid'),
        $tid        = $that.data('tid'),
        $actionUrl  = $that.data('url') || $that.attr("href"),
        $modal      = $('#editTokenModal'),
        $modalBody  = $modal.find('.modal-body'),
        $ajaxLoader = $('#ajaxContainerLoading2'),
        $oldModalBody   = $modalBody.html();
        modalContent = $modal.find('#modal-content');
    $ajaxLoader.show();
    modalContent.empty();
    $modal.modal('show');

    // Ajax request
    $.ajax({
        url : $actionUrl,
        type : 'GET',

        // html contains the buttons
        success : function(html, status){

            // Fake hide of modal content, so we can still get width of inner elements like labels
            var previousCss  = modalContent.attr("style");
            modalContent
                .css({
                    position:   'absolute', // Optional if #myDiv is already absolute
                    visibility: 'hidden',
                    display:    'block'
                });

            modalContent.append(html);                       // Inject the returned HTML in the modal body

            // Apply the yes/no/date jquery plugin to the elements loaded via ajax
            /*
                $('#sent-yes-no-date-container').YesNoDate();
                $('#remind-yes-no-date-container').YesNoDate();
                $('#completed-yes-no-date-container').YesNoDate();
            */

            $('.yes-no-date-container').each(function(el){
                $(this).YesNoDate().onReadyMethod();
            });


            $('.yes-no-container').each(function(el){
                $(this).YesNo().onReadyMethod();
            });

            $('#validfrom').datetimepicker({locale: $('#validfrom').data('locale')});
            $('#validuntil').datetimepicker({locale: $('#validuntil').data('locale')});

            $('.date .input-group-addon').on('click', function(){
                $prev = $(this).siblings();
                $prev.data("DateTimePicker").show();
            });

            var elGeneral  = $('#general');

            // Stick the labels on the left side
            // Sometime, the content is loaded after modal is shown, sometimes not. So, we wait 200ms just in case (For label width)
            setTimeout(function(){
                elGeneral.stickLabelOnLeft();
                $ajaxLoader.hide();
                // Remove fake hide
                modalContent.attr("style", previousCss ? previousCss : "");
            }, 200);

        },
        error :  function(html, status){
            $ajaxLoader.hide();
            modalContent.append(html);
            console.ls.error(html);
        }
    });
    return false;
};

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


function centerInfoDialog() {
    var infoDialog = $("#info_dialog");
    var dialogparent = infoDialog.parent();
    infoDialog.css({ 'left': Math.round((dialogparent.width() - infoDialog.width()) / 2)+'px' });
}

function onUpdateTokenGrid(){
    reinstallParticipantsFilterDatePicker();
    $('.edit-token').off('click.edittoken').on('click.edittoken', startEditToken);
}

/**
 * When date-picker is used in token gridview
 * @return
 */
function reinstallParticipantsFilterDatePicker() {

    // Since grid view is updated with Ajax, we need to fetch date format each update
    var dateFormatDetails = JSON.parse($('input[name="dateFormatDetails"]').val());

    $('#TokenDynamic_validfrom').datetimepicker({
        format: dateFormatDetails.jsdate + ' HH:mm'
    });
    $('#TokenDynamic_validuntil').datetimepicker({
        format: dateFormatDetails.jsdate + ' HH:mm'
    });

    $('#TokenDynamic_validfrom').on('focusout', function() {
        var data = $('#token-grid .filters input, #token-grid .filters select').serialize();
        $.fn.yiiGridView.update('token-grid', {data: data});
    });

    $('#TokenDynamic_validuntil').on('focusout', function() {
        var data = $('#token-grid .filters input, #token-grid .filters select').serialize();
        $.fn.yiiGridView.update('token-grid', {data: data});
    });
    $(document).trigger('actions-updated');

}
