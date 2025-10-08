/**
 * Massive actions Widget, action behaviour
 *
 * This JavaScript define what's happen when user click on an action
 */

/**
 * Define what happen when an action is clicked:
 *
 * - redirection:
 *      post
 *      fill session
 *
 * - Show a validation modal:-
 *      perform an ajax request and close
 *      perform an ajax request and show the result in the modal
 */
var onClickListAction =  function () {
    console.log('onClickListAction');
    if($(this).data('disabled')) {
        console.log('disabled');
        return;
    }
    var $that          = $(this);                                                             // The clicked link
    var $actionUrl     = $that.data('url');                                                   // The url of the Survey Controller action to call
    var onSuccess      = $that.data('on-success');
    var $gridid        = $('#'+$(this).closest('div.listActions').data('grid-id'));
    var $grididvalue   = $gridid.attr('id');
    var $oCheckedItems = $gridid.yiiGridView('getChecked', $(this).closest('div.listActions').data('pk')); // List of the clicked checkbox
    $oCheckedItems = JSON.stringify($oCheckedItems);
    var actionType     = $that.data('actionType');   
    var selectedList   = $(".selected-items-list");

    if ($oCheckedItems == '[]') {
        //If no item selected, the error modal "please select first an item" is shown
        // TODO: add a variable in the widget to replace "item" by the item type (e.g: survey, question, token, etc.)
        console.log('error first');
        const modal = new bootstrap.Modal(document.getElementById('error-first-select' + $grididvalue), {})
        modal.show();
        return;
    }
    
    
    // TODO : Switch action (post, session, ajax...)

    // For actions without modal, doing a redirection
    // TODO: replace all of them with the method above

    // TODO : Switch case "redirection (with 2 type; post or fill session)"
    if(actionType == "redirect")
    {
        $oCheckedItems = $gridid.yiiGridView('getChecked', $('.listActions').data('pk')); // So we can join
        var newForm = jQuery('<form>', {
            'action': $actionUrl,
            'target': $that.data('target') ?? '_blank',
            'method': 'POST'
        }).append(jQuery('<input>', {
            'name': $that.data('input-name'),
            'value': $oCheckedItems.join($that.data('input-separator') ?? '|'),
            'type': 'hidden'
        })).append(jQuery('<input>', {
            'name': LS.data.csrfTokenName,
            'value': LS.data.csrfToken,
            'type': 'hidden'
        })).appendTo('body');
        newForm.submit();
        console.log('redirect');
        return;
    }

    // For actions without modal, doing a redirection
    // Using session before redirect rather than form submission
    if(actionType == 'fill-session-and-redirect')
    {
        // postUrl is defined as a var in the View, if not the basic url is used
        var setSessionUrl = postUrl || $actionUrl;
        $(this).load(setSessionUrl, {
            itemsid:$oCheckedItems},function(){
                $(location).attr('href',$actionUrl);
            });
        console.log('fill session');
        return;
    }

    // Set window location href. Used by download files in responses list view.
    if (actionType == 'window-location-href') {
        var $oCheckedItems = $gridid.yiiGridView('getChecked', $('.listActions').data('pk')); // So we can join
        console.log('href = ...');
        window.location.href = $actionUrl + $oCheckedItems.join(',');
        return;
    }

    /**
     * Custom action
     * Will run Javascript function in 'custom-js'. First argument is array of item ids, defined by 'pk'.
     */
    if (actionType == 'custom') {
        var js = $that.data('custom-js');
        var func = eval(js);
        var itemIds = $gridid.yiiGridView('getChecked', $('.listActions').data('pk'));
        func(itemIds);
        console.log('func itemIds');
        return;
    }

    // TODO: switch case "Modal"
    var $modal  = $('#'+$that.data('modal-id'));   // massive-actions-modal-<?php echo $this->gridid;?>-<?php $aAction['action'];?>-<?php echo $key; ?>

    // Needed modal elements
    var $modalTitle    = $modal.find('.modal-title');                   // Modal Title
    var $modalBody     = $modal.find('.modal-body-text');               // Modal Body
    var $modalButton   = $modal.find('.btn-ok');

    var $modalClose    = $modal.find('.modal-footer-close');            // Modal footer with close button
    var $ajaxLoader    = $("#ajaxContainerLoading");                    // Ajax loader

    // Original modal state
    var $oldModalTitle     = $modalTitle.text();
    var $oldModalBody      = $modalBody.html();
    var $oldModalButtons   = $modal.find('.modal-footer-buttons');     // Modal footer with yes/no buttons
    var $modalShowSelected = $modal.data('show-selected');
    var $modalSelectedUrl = $modal.data('selected-url');
    
    //Display selected data in modals after clicked on action
    if($modalShowSelected == 'yes' && $modalSelectedUrl ){  
        
        //set csrfToken for ajaxpost
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        
        //clear selected list view 
        selectedList.empty();

        console.log('before ajax');
        //ajaxpost to set data in the selected items div 
        $.ajax({
            url :$modalSelectedUrl,
            type : 'POST',
            data : {$grididvalue, $oCheckedItems,csrfToken},
            success: function(html, statut){    
                selectedList.html(html);
            },
            error: function(requestObject, error, errorThrown){
                    console.log(error);
            }
        });           
    }

    // When user close the modal, we put it back to its original state
    $modal.on('hidden.bs.modal', function (e) {
        $modalTitle.text($oldModalTitle);               // the modal title
        $modalBody.empty().append($oldModalBody);       // modal body
        $modalClose.hide();                             // Hide the 'close' button
        $oldModalButtons.show();                        // Show the 'Yes/No' buttons

        if ($that.data('grid-reload') == "yes")
        {
            $gridid.yiiGridView('update');                         // Update the surveys list
            setTimeout(function(){
                $(document).trigger("actions-updated");}, 500);    // Raise an event if some widgets inside the modals need some refresh (eg: position widget in question list)
        }

    })

    /* Define what should be done when user confirm the mass action */
    /* remove all existing action before adding the new one */
    $modalButton.off('click').on('click', function(){
        var $form = $modal.find('form');
        if ($form.data('trigger-validation')) {
            if (!$form[0].reportValidity()) {
                return;
            }
        }

        // Custom datas comming from the modal (like sid)
        var $postDatas  = {sItems:$oCheckedItems};
        $modal.find('.custom-data').each(function(i, el)
        {
            if ($(this).hasClass('btn-group')){ // ext.ButtonGroupWidget.ButtonGroupWidget
                $(this).find('input:checked').each(function(i, el)
                {
                    $postDatas[$(this).attr('name')]=$(this).val();
                });
            } else if ($(this).attr('type') == 'checkbox') {
                if ($(this).prop('checked')) {
                    $postDatas[$(this).attr('name')]=$(this).val();
                }
            } else {
                $postDatas[$(this).attr('name')]=$(this).val();
            }
        });

        // Custom attributes to updates (like question attributes)
        var aAttributesToUpdate = [];
        $modal.find('.attributes-to-update').each(function(i, el)
        {
            aAttributesToUpdate.push($(this).attr('name') || $(this).attr('id'));
        });
        $postDatas['aAttributesToUpdate'] = JSON.stringify(aAttributesToUpdate);
        $postDatas['grididvalue'] = $grididvalue;

        $modal.find('input.post-value, select.post-value').each(function(i, el) {
            $postDatas[$(el).attr('name')] = $(el).val();
        });

        // Update the modal elements
        // TODO: ALL THIS DEPEND ON KEEPOPEN OR NOT
        $modalBody.empty();                                         // Empty the modal body
        $oldModalButtons.hide();                                    // Hide the 'Yes/No' buttons
        $modalClose.show();                                         // Show the 'close' button
        $ajaxLoader.show();                                         // Show the ajax loader
        selectedList.empty();                                       //clear selected Item list

        // Ajax request
        $.ajax({
            url : $actionUrl,
            type : 'POST',
            data :  $postDatas,

            // html contains the buttons
            success : function(html, statut){
                $ajaxLoader.hide();                                 // Hide the ajax loader

                if( $modal.data('keepopen') != 'yes' )
                {
                    $modal.modal('hide');
                }
                else
                {
                    // This depend on keepopen
                    $modalBody.empty().html(html);                      // Inject the returned HTML in the modal body
                }

                if (html.ajaxHelper) {
                    LS.AjaxHelper.onSuccess(html);
                    return;
                }

                if (onSuccess) {
                    var func = eval(onSuccess);
                    func(html);
                    return;
                }
            },
            error: function(data, textStatus, jqXHR) {
                $ajaxLoader.hide();
                if (data
                    && data.responseJSON
                    && data.responseJSON.success === false
                    && data.responseJSON.message)
                {
                    $modal.modal('hide');
                    LS.LsGlobalNotifier.createAlert(data.responseJSON.message,  "danger", {showCloseButton: true});
                } else {
                    $modal.find('.modal-body-text').empty().html(data.responseText);
                }
            }
        });
    });

    // Open the modal
    const modalId = $that.data('modal-id');
    console.log('modalId = ', modalId);
    var modal = new bootstrap.Modal(document.getElementById(modalId), {})
    modal.show();
};

/**
 * Bootstrap switch extension
 *
 * 1. Setting the value
 * By default, bootstrap switch use the val() jQuery function, which works well for form submission.
 * But, for the ajax request, we need to collect the value in a array for the post using $postDatas[$(this).attr('name')]=$(this).val();
 * So we need to change the value using $(this).attr('value', state);.
 * The difference can be seen visually in the browser code explorer : by default, the bootstrap switch extension change in an invisble way.
 * With the method here, the input value change will be visible.
 *
 * 2. Defining value Type
 * By default, bootstrap switch use boolean values {true,false} for its states.
 * In the PHP code (like in controller questionEditor::changeMultipleQuestionAttributes()), we want to keep the code as dry as possible.
 * To avoid to create a single method for each action using bootstrap-switch, just to change the boolean value to something else ({1,0} or {Y,N}, etc), we perform it here.
 * e.g: a bootstrap-switch with the class bootstrap-switch-integer will have its value converted to integer.
 *
 * 3. Managing grid refresh
 * For now, the modals are injected on the bottom of the selector, which is in the grid footer, which is reload on grid refresh
 * So, when refreshing the grid, the bootstrap-switch must be re-applyed to the elements
 *
 */
// TODO: It seems below two functions are not used and can be deleted. Please confirm.
 function prepareBsSwitchBoolean($gridid){
     // Bootstrap switch with class "bootstrap-switch-boolean" will use the default boolean values.
     // e.g: question mandatory, question other, etc
     $('.bootstrap-switch-boolean').each(function(){
         $(this).attr('value', false);                                           // we specify its value in a "visible" way (see point 1)

         // Switch change
         $(this).on('switchChange.bootstrapSwitch', function(event, state) {
             $(this).attr('value', state);                                       // When the switch change,we specify its value in a "visible" way (see point 1)
         });
     });
}

function prepareBsSwitchInteger($gridid){
    // Bootstrap switch with class "bootstrap-switch-integer" will use integer values
    // e.g: question statistics_showgraph, question public_statistics, etc
    $('.bootstrap-switch-integer').each(function(){
        $(this).attr('value', 0);                                               // we specify its value in a "visible" way (see point 1)

        // Switch change
        $(this).on('switchChange.bootstrapSwitch', function(event, state) {
            var intValue = (state==true)?'1':'0';                               // Convertion of the boolean to integer (see point 2)
            $(this).attr('value', intValue)
        });
    });
}
// =================================================================================


function prepareBsDateTimePicker($gridid){
    var dateTimeSettings = getDefaultDateTimePickerSettings();
    if (dateTimeSettings) {
        var dateTimeFormat = dateTimeSettings.dateformatsettings.jsdate+ ' HH:mm';
        $('.date input').each(function(){
            $(this).datetimepicker({
                format: dateTimeFormat,
                showClear: dateTimeSettings.showClear,
                allowInputToggle: dateTimeSettings.allowInputToggle,
            });
    });
    }
}

// get user session datetimesettings
function getDefaultDateTimePickerSettings() {
    // TODO: Code below can't handle if installation is in a subfolder (not web root).
    // The correct solution is to fetch datetime format from an <input> element.
    return null;

    //Switch between path and get based routing
    if(/\/index\.php(\/)?\?r=admin/.test(window.location.href)){
        var url = "/index.php?r=surveyAdministration/datetimesettings";
    } else {
        var url = "/index.php/surveyAdministration/datetimesettings";
    }
    var mydata = [];
    $.ajaxSetup({
        async: false
    });
    $.getJSON( url, function( data ) {
        mydata = data;
    });
    return mydata;
}

function bindListItemclick() {
    let listActions = $('.listActions a');
    let listActionsDisabled = $('.listActions .disabled a');
    listActions.off('click.listactions').on('click.listactions', onClickListAction);
    listActionsDisabled.off('click.listactions').on('click.listactions', function (e) {
        e.preventDefault();
    });
}


$(document).off('pjax:scriptcomplete.listActions').on('pjax:scriptcomplete.listActions, ready ', function() {
    prepareBsSwitchBoolean(gridId);
    prepareBsSwitchInteger(gridId);

    // Grid refresh: see point 3
    $(document).on('actions-updated', function(){
        prepareBsSwitchBoolean(gridId);
        prepareBsSwitchInteger(gridId);
        prepareBsDateTimePicker(gridId);
        bindListItemclick();
    });
    bindListItemclick();
});


function switchStatusOfListActions(e) {
    var checkboxSelector = '.grid-view-ls input[type="checkbox"]';
    // Attach an onchange event handler to all checkboxes
    $(document).on('change', checkboxSelector, function () {
        // This assumes there is only one massive and one grid in the page.
        // @todo: 
        // - Stamp the related grid-id in the massive action button (see massive action widget).
        // - From checkbox traverse to grid. Fetch grid id.
        // - Use grid-id to get a more robust link in between grid and massive actions.
        var actionButton = $('.massiveAction');
        if (isAnyCheckboxChecked()) {
            actionButton.removeClass('disabled');
        } else {
            actionButton.addClass('disabled');
        }
    });
}

// Function to check if at least one checkbox is checked
function isAnyCheckboxChecked() {
    // This assumes there is only one checkbox per row
    // - Make isAnyCheckboxChecked() to only check the first one
    // or
    // - Stamp on the MassiveActions widget the checkbox class for the row selector and the header
    // - Use that class to only query selector checkboxes
    return $('.grid-view-ls table tbody input[type="checkbox"]:checked').length > 0;
}

['DOMContentLoaded','ready', 'pjax:scriptcomplete'].forEach(function (e) {
    document.addEventListener(e, () => {
        switchStatusOfListActions();
    });
});
