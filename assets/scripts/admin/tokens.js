
var LS = LS || {
    onDocumentReady: {}
};

/**
 *
 * @type {{}}
 */
var filterData = {};

Tokens = {
     /**
      * jQuery Plugin to manage the date in token modal edit.
      * Some fields, like "Completed", can have string value (eg: 'N') or a date value.
      * They are displayed via a switch hidding or showing a date picker.
      */
     YesNoDate: function (el) {
         var $elSwitch = el.querySelector('.YesNoDateSwitch'),           // switch element (generated with YiiWheels widgets)
             $elDateContainer = el.querySelector('.date-container'),            // date time picker container (to show/hide)
             $elDate = el.querySelector('.YesNoDatePicker'),           // date time picker element (generated with YiiWheels widgets)
             $elHiddenInput = el.querySelector('.YesNoDateHidden');           // input form, containing the value to submit to the database
         console.ls.log('tokenform', {
             $elSwitch: $elSwitch,
             $elDateContainer: $elDateContainer,
             $elDate: $elDate,
             $elHiddenInput: $elHiddenInput
         });

         // Generate the date time picker
         initDatePicker($elDate);

         console.ls.log('$elSwitch', $elSwitch);
         // When user switch
         $elSwitch.addEventListener('change', (event, state) => {
             console.ls.log('$elSwitch', event, state);
             if ($elSwitch.querySelector('input').checked) {
                 // Show date

                 $elDateContainer.classList.remove('d-none');
                 $elHiddenInput.value = $elDate.value = moment().format($elDate.dataset.format);
             } else {
                 // Hide date, set hidden input to "N"
                 $elDateContainer.classList.add('d-none');
                 $elHiddenInput.value = 'N';
             }
         });

         // When user change date
         $elDate.addEventListener('change', function (e) {
             $elHiddenInput.value = $elDate.value;
         });
     },
     YesNo: function (el) {
         let $elHiddenInput = el.querySelector('.YesNoDateHidden');           // input form, containing the value to submit to the database
         let $elSwitch = el.querySelector('.YesNoSwitch');               // switch element (generated with YiiWheels widgets)
         // When user change date
         $elSwitch.addEventListener('change', () => {
             if ($elSwitch.querySelector('input').checked) {
                 $elHiddenInput.value = 'Y';
             } else {
                 $elHiddenInput.value = 'N';
             }
         });
     }
 };

/**
 * Provide to this function a element containing form-groups,
 * it will stick the text labels on its border
 * @TODO Does this function still make sense???
 */
$.fn.stickLabelOnLeft  = function(options)
{
    var that = $(this);
    var formgroups = that.find('.ex-form-group');
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
                $modal.hide();
                $('body').removeClass('modal-open');
                $('body').removeAttr('style');
                $('.modal-backdrop').remove();
                window.LS.ajaxAlerts(result.success, 'success');
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
                        $modal.hide();
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                    } // Update the surveys list
                });
            }
            catch (e){
                if (e) {
                    console.ls.error(e);
                    $modal.hide();
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
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
    const validationErrorMsg = $('#edittoken').attr('data-additional-attributes-validation-error');

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
        const modalElement = document.getElementById('emptyTokenConfirmationModal');
        const modal = new bootstrap.Modal(modalElement);
        modalElement.addEventListener('hidden.bs.modal', function () {
            // Enable the Save and Close button
            $("#save-and-close-button").removeClass("disabled");
        });
        modal.show();
        $('#ls-loading').hide();
        return false;
    }
    return true;
}

/**
 * Scroll the pager and the footer when scrolling horizontally
 */
$(document).on('ready pjax:scriptcomplete', function(){

    if($('#sent-yes-no-date-container').length > 0)
    {
        $('#general').stickLabelOnLeft();

        document.querySelectorAll('.yes-no-date-container').forEach((el) => {
            Tokens.YesNoDate(el);
        });

        document.querySelectorAll('.yes-no-container').forEach((el) => {
            Tokens.YesNo(el);
        });
        initValidFromValidUntilPickers();
    }

    var modal = $('#massive-actions-modal-edit-0');
    if (modal.length) {
        modal.on('shown.bs.modal', function () {
            $('.yes-no-date-container').each(function(i,el){
                Tokens.YesNoDate(el);
            });

            $('.yes-no-container').each(function(i,el){
                Tokens.YesNo(el);
            });
        });
    }

    $(document).on('actions-updated', function() {
        $('.yes-no-date-container').each(function(i,el){
            Tokens.YesNoDate(el);
        });

        $('.yes-no-container').each(function(i,el){
            Tokens.YesNo(el);
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
        const modal = new bootstrap.Modal(document.getElementById('confirmation-modal'));
        modal.show();
        $modal.find('.modal-footer-yes-no').find('a.btn-ok').on('click', function(click){
            $.ajax({
                url: actionUrl,
                method: "GET",
                success: function(data){

                    $('#token-grid').yiiGridView('update',{
                        complete: function(s){
                            modal.hide();
                        } // Update the surveys list
                    });
                }
            });
        })
    });

    $(document).off('click.edittoken', '.edit-token').on('click.edittoken', '.edit-token', function (event) {
        startEditToken(event, $(this));
    });

    $(document).off('submit.edittoken', '#edittoken').on('submit.edittoken', '#edittoken', function (event, params) {
        var eventParams = params || {};
        // When saving from the Edit Participant modal, handle the event in submitEditToken().
        if($('#editTokenModal').length > 0 ){
            event.preventDefault();
            submitEditToken();
            return;
        }
        // Validate additional (custom) participant attributes
        if (!validateAdditionalAttributes()) {
            event.preventDefault();
            return false;
        }
        // Validate expiration date isn't lower than the "Valid from" date
        if (
            !LS.validateEndDateHigherThanStart(
                $('#validfrom').data('DateTimePicker'),
                $('#validuntil').data('DateTimePicker'),
                () => {
                    showError($('#edittoken').attr('data-expiration-validation-error'));
                    $('#validuntil').trigger('invalid');
                }
            )
        ) {
            event.preventDefault();
            return false;
        }

        if (!eventParams.confirm_empty_save && !validateNotEmptyTokenForm()) {
            return false;
        }
    });

    /**
     * Handle form inputs 'invalid' event.
     */
    $('#edittoken').find('button, input, select, textarea').on('invalid', function () {
        // Enable the Save and Close button
        $("#save-and-close-button").removeClass("disabled");
    });

    /**
     * Save token
     */
    $("#save-edittoken").off('click.token-save').on('click.token-save', function() {
        const valid = validateAdditionalAttributes()
            && LS.validateEndDateHigherThanStart(
                $('#validfrom').data('DateTimePicker'),
                $('#validuntil').data('DateTimePicker'),
                () => {showError($('#edittoken').attr('data-expiration-validation-error'))}
            );
        if (valid) {
            submitEditToken();
        }
    });

    /**
     * Confirm save empty token
     */
    $("#save-empty-token").off('click.token-save').on('click.token-save', function() {
        $('#ls-loading').show();
        $('#edittoken').trigger('submit', {confirm_empty_save: true});
    });


    $('#startbounceprocessing').click(function(){

        var $that               = $(this);
        var $url                = $that.data('url');
        var $modal              = $('#tokenBounceModal');
        const modal             = new bootstrap.Modal(document.getElementById('tokenBounceModal'));
        var $ajaxLoader         = $('#ajaxContainerLoading');
        var $modalBodyText      = $modal.find('.modal-body-text');
        var $limebutton         = $modal.find('.modal-footer .limebutton');

        $modalBodyText.empty();
        $limebutton.empty().append('close');
        $ajaxLoader.show();
        modal.show();

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
        <td><span data-bs-toggle='tooltip' title='" + sDelete + "' class='ui-pg-button ri-delete-bin-fill text-danger' onClick= $(this).parent().parent().remove();$('#joincondition_"+conditionid+"').remove() id='ui-icon removebutton'"+conditionid+"></span>\n\
        <span data-bs-toggle='tooltip' title='" + sAdd + "' class='ui-pg-button addcondition-button ui-icon text-success ri-add-circle-fill' style='margin-bottom:4px'></span></td></tr><tr></tr>";
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
var startEditToken = function(event, that){
        event.preventDefault()
        $sid        = $(that).data('sid'),
        $tid        = $(that).data('tid'),
        $actionUrl  = $(that).data('url') || $(that).attr("href"),
        $modal      = $('#editTokenModal'),
        $modalBody  = $modal.find('.modal-body'),
        $ajaxLoader = $('#ajaxContainerLoading2'),
        $oldModalBody   = $modalBody.html();
        modalContent = $modal.find('#modal-content');
    $ajaxLoader.show();
    modalContent.empty();
    const modal = new bootstrap.Modal(document.getElementById('editTokenModal'));
    modal.show();

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

            document.querySelectorAll('.yes-no-date-container').forEach((el) => {
                Tokens.YesNoDate(el);
            });

            document.querySelectorAll('.yes-no-container').forEach((el) => {
                Tokens.YesNo(el);
            });

            initValidFromValidUntilPickers();

            $('.date .input-group-text').on('click', function(){
                $prev = $(this).siblings();
                // $prev.data("DateTimePicker").show();
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

function onUpdateTokenGrid() {
    reinstallParticipantsFilterDatePicker();
    $('.edit-token').off('click.edittoken').on('click.edittoken', function (event) {
        startEditToken(event, $(this));
    });
}

/**
 * When date-picker is used in token gridview
 * @return
 */
function reinstallParticipantsFilterDatePicker() {
    // Since grid view is updated with Ajax, we need to fetch date format each update
    var dateFormatDetails = document.getElementById('dateFormatDetails');
    var locale = document.getElementById('locale');
    var validfromElement = document.getElementsByName('TokenDynamic[validfrom]')[0];
    var validuntilElement = document.getElementsByName('TokenDynamic[validuntil]')[0];
    if ((dateFormatDetails && dateFormatDetails.value) && (locale && locale.value)) {
        dateFormatDetails = JSON.parse(dateFormatDetails.value);
        var dateFormat = dateFormatDetails.jsdate + ' HH:mm';
        if (validfromElement) {
            initDatePicker(validfromElement, locale.value, dateFormat);
            validfromElement.addEventListener("hide.td", function () {
                reloadTokenGrid();
            });
        }
        if (validuntilElement) {
            initDatePicker(validuntilElement, locale.value, dateFormat);
            validuntilElement.addEventListener("hide.td", function () {
                reloadTokenGrid();
            });
        }
    }
    $(document).trigger('actions-updated');
}

/**
 * reload gridview only when data of filter input has changed
 */
function reloadTokenGrid() {
    var newData = $('#token-grid .filters input, #token-grid .filters select').serialize();
    if (filterData !== newData) {
        filterData = newData;
        $.fn.yiiGridView.update('token-grid', {data: filterData});
    }
}

function initValidFromValidUntilPickers() {
    var validfromElement = document.getElementById('validfrom');
    var validuntilElement = document.getElementById('validuntil');
    var dateFormat = validfromElement.dataset.dateformat;
    var locale = validfromElement.dataset.locale;
    if (validfromElement) {
        initDatePicker(validfromElement, 'validfrom', locale, dateFormat);
    }
    if (validuntilElement) {
        initDatePicker(validuntilElement, 'validuntil', locale, dateFormat);
    }
}
