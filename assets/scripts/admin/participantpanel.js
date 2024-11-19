// Namespace
var LS = LS || {
    onDocumentReady: {}
};
LS.CPDB = (function() {
    var
    // Basic modal used by all submethods
    baseModal = '#participantPanel_edit_modal',

    /**
     * @param {string} url
     * @param {object} data
     * @param {string} actionButtonClass
     * @param {string} formId
     * @oaram {string} gridViewId
     * @return
     */
    runBaseModal = function(url, data, actionButtonClass, formId, gridViewId, callback){

        callback = callback || function(){};
        /**
         * @param {object} result
         * @todo
         */
        var secondSuccess = function(result) {
            $(baseModal).modal('hide');
            $.fn.yiiGridView.update(gridViewId,{});
            callback(result);
        };

        /**
         * @param {string} page - Modal HTML fetched with Ajax
         * @todo
         */
        var firstSuccess = function(json){
            $(baseModal).find('.modal-content').html(json.result);
            $(baseModal).modal('show');
            $(baseModal).find('.'+actionButtonClass).on('click', function(e) {
                e.preventDefault();
                var action = $(baseModal).find('#'+formId).attr('action');
                var formData = $(baseModal).find('#'+formId).serializeArray();
                return $.ajax({
                    url: action,
                    data: formData,
                    method: 'POST',
                    success: secondSuccess,
                    error : function() {
                        console.ls.log(arguments);
                    }
                });
            });
        };


        return $.ajax({
            url: url,
            data: data,
            method: 'POST',
            success: firstSuccess,
            error: function(){ console.ls.log(arguments) }
        });
    },

    /**
     * Run when user clicks 'Export'
     * Used for both all participants and checked participants
     * @param {boolean} all - If true, export all participants
     * @return
     */
    onClickExport = function(all) {
        var postdata = {
            selectedParticipant: [],
        }; /* csrf is already in ajaxSetup */

        if (!all) {
            $('.selector_participantCheckbox:checked').each(function(i,item){
                postdata.selectedParticipant.push($(item).val());
            });
        }
        $.ajax({
            url: exporttocsvcountall,
            data: postdata,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            method: 'POST',

            /**
             * @param {string} data
             * @return
             */
            success:  function(data) {
                count = data;
                if(count == 0)
                {
                    $('#exportcsvallnorow').modal('show');
                    $('#exportcsvallnorow').on('shown.bs.modal', function(e) {
                        $(this).find('.exportButton').remove();
                    });
                }
                else
                {
                    $('#exportcsv').modal('show');
                    $('#exportcsv').on('shown.bs.modal', function(e) {
                        var self = this;
                        $('#exportcsv').find('h5.modal-title').text(count);
                        $(this).find('.exportButton').on('click', function() {
                            var dldata = postdata;
                            var val = $('#attributes').val();
                            if (val) {
                                dldata.attributes = val.join('+');
                            } else {
                                dldata.attributes = '';
                            }
                            var dlForm = $("<form></form>")
                                .attr('action', exportToCSVURL)
                                .attr('method', "POST");
                            /* add crsf sice where out of ajax here */
                            $('<input />')
                                .attr('name', LS.data.csrfTokenName)
                                .attr('value', LS.data.csrfToken)
                                .appendTo(dlForm);
                            $.each(dldata, function(key,value){
                                $('<input />')
                                    .attr('name', key)
                                    .attr('value', value)
                                    .appendTo(dlForm);
                            });
                            dlForm.css('display', 'none').appendTo('body').submit();
                            $(self).modal("hide");
                        });

                        $('#select-all').click(function () {
                            if ($('#select-all').is(':checked')) {
                                $('#attributes > option').prop('selected', 'selected');
                                $('#attributes').trigger('change');
                            } else {
                                $('#attributes > option').removeAttr('selected');
                                $('#attributes').trigger('change');
                            }
                        });
                    });
                    /* $.download(exporttocsvall,'searchcondition=dummy',$('#exportcsvallprocessing').dialog("close"));*/
                }
            }
        });
    },

    // Basic settings and bindings that should take place in all three views
    basics = function() {
        // Code for AJAX download
        jQuery.download = function(url, data, method){
            //url and data options required
            if( url && data ){
                //data can be string of parameters or array/object
                data = typeof data == 'string' ? data : jQuery.param(data);
                //split params into form inputs
                var inputs = '<input type="hidden" name="'+LS.data.csrfTokenName+'" value="'+LS.data.csrfToken+'">';
                jQuery.each(data.split('&'), function(){
                    var pair = this.split('=');
                    inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'">';
                });
                //send request
                jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
                    .appendTo('body').submit().remove();
            };
        };
    },

    // JS-bindings especially for the participantPanel
    participantPanel = function(){

        $('#removeAllFilters').on('click', function(e){
            e.preventDefault();
            $('#searchcondition').val('');
            $('#ParticipantFilters').remove();
            $.fn.yiiGridView.update('list_central_participants',{});
            return false;
        });

        $('.action_participant_editModal').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editparticipant', 'participant_id' : $(this).data('participantId')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editParticipant',
                'editPartcipantActiveForm',
                'list_central_participants',
                function (result) {
                    if (!result.error) {
                        window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                    }
                }
            );
        });

        $('.action_participant_deleteModal').on('click', function(e) {
            e.preventDefault();
            var data = {modalTarget: 'showdeleteparticipant', 'participant_id' : $(this).data('participantId')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_deleteParticipant',
                'deleteParticipantActiveForm',
                'list_central_participants',
                function (result) {
                    if (!result.error) {
                        window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                    }
                }
                );
        });
        $('.action_participant_infoModal').on('click', function(e) {
            e.preventDefault();
            var data = {
                modalTarget: 'showparticipantsurveys',
                participant_id: $(this).data('participantId')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                    openModalParticipantPanel,
                    data,
                    'action_save_modal_deleteParticipant',
                    'deleteParticipantActiveForm',
                    'list_central_participants'
                    );
        });
        $('.action_participant_shareParticipant').on('click', function(e) {
            e.preventDefault();
            var data = {modalTarget: 'shareparticipant', 'participant_id' : $(this).data('participantId')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_shareparticipant',
                'shareParticipantActiveForm',
                'list_central_participants',
                function(result) {
                    if (!result.error) {
                        window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                    }
                }
            );
        });

        $('#addParticipantToCPP').on('click', function(e){
            e.preventDefault();
            var data = {
                modalTarget: 'editparticipant'
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editParticipant',
                'editPartcipantActiveForm',
                'list_central_participants',
                function(result) {
                    console.ls.log(result);
                    if(!result.error) {
                        window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                    }
                }
            );
        });

        /**
         * Small icon, add participant to a survey
         */
        $('.action_participant_addToSurvey').on('click', function(e) {
            var data = {
                modalTarget: 'addToSurvey',
                participant_id: $(this).data('participantId')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_addToSurvey',
                'addToSurveyActiveForm',
                'list_central_participants'
            );
        });

        // Toggle all, participant list
        $('#action_toggleAllParticipant').on('click', function() {
            $('.selector_participantCheckbox').prop('checked',$('#action_toggleAllParticipant').prop('checked'));
        });

        // Toggle all, share panel
        $('#action_toggleAllParticipantShare').on('click', function() {
            $('.selector_participantShareCheckbox').prop('checked', $('#action_toggleAllParticipantShare').prop('checked'));
        });

        let changeBlacklistButtons = document.querySelectorAll('.action_changeBlacklistStatus input');
        for (let changeBlacklistButton of changeBlacklistButtons) {
            changeBlacklistButton.addEventListener("change", (event) => {
                let params = "actionTarget=changeBlacklistStatus"
                    + "&participant_id=" + event.target.closest("tr").dataset.participant_id
                    + "&blacklist=" + event.target.value
                    + "&YII_CSRF_TOKEN=" + LS.data.csrfToken;
                let xhttp = new XMLHttpRequest();
                xhttp.open("POST", editValueParticipantPanel, true);
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send(params);
            });
        }

        $('#pageSizeParticipantView').on("change", function(){
            $.fn.yiiGridView.update('list_central_participants',{ data:{ pageSizeParticipantView: $(this).val() }});
        });
        bindListItemclick();

        if($('#export').hasClass('d-none')){
            $('#export').removeClass('d-none');
        }
    },
    //JS-bindings especially for the attributePanel
    attributePanel = function(){
        $('#addParticipantAttributeName').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editattribute'};
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editAttributeName',
                'editAttributeNameActiveForm',
                'list_attributes',
                function(result) {
                    console.ls.log(result);
                    if(!result.error) {
                        window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                    }
                }
            );
        });
        $('.action_attributeNames_editModal').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editattribute','attribute_id' : $(this).data('attribute_id')};
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_editAttributeName',
                'editAttributeNameActiveForm',
                'list_attributes'
            );
        });

        $('#action_toggleAllAttributeNames').on('click', function(){
            $('.selector_attributeNamesCheckbox').prop('checked',$('#action_toggleAllAttributeNames').prop('checked'));
        });

        let changeAttributeVisibilityButtons = document.querySelectorAll('.action_changeAttributeVisibility input');
        for (let changeAttributeVisibilityButton of changeAttributeVisibilityButtons) {
            changeAttributeVisibilityButton.addEventListener("change", (event) => {
                let params = "actionTarget=changeAttributeVisibility"
                    + "&attribute_id=" + event.target.closest("tr").dataset.attribute_id
                    + "&visible=" + event.target.value
                    + "&YII_CSRF_TOKEN=" + LS.data.csrfToken;
                let xhttp = new XMLHttpRequest();
                xhttp.open("POST", editValueParticipantPanel, true);
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send(params);
            });
        }

        let changeAttributeEncryptedButtons = document.querySelectorAll('.action_changeAttributeEncrypted input');
        for (let changeAttributeEncryptedButton of changeAttributeEncryptedButtons) {
            changeAttributeEncryptedButton.addEventListener("change", (event) => {
                let params = "actionTarget=changeAttributeEncrypted"
                    + "&attribute_id=" + event.target.closest("tr").dataset.attribute_id
                    + "&encrypted=" + event.target.value
                    + "&YII_CSRF_TOKEN=" + LS.data.csrfToken;
                let xhttp = new XMLHttpRequest();
                xhttp.open("POST", editValueParticipantPanel, true);
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send(params);
            });
        }

        $('#pageSizeAttributes').on("change", function(){
            $.fn.yiiGridView.update('list_attributes',{ data:{ pageSizeAttributes: $(this).val() }});
        });

        if(!$('#export').hasClass('d-none')){
            $('#export').addClass('d-none');
        }
    },
    //JS-bindings especially for the sharePanel
    sharePanel = function() {
        $('#action_toggleAllParticipant').on('click', function(){
            $('.selector_participantCheckbox').prop('checked', $('#action_toggleAllParticipant').prop('checked'));
        });

        let changeSharedEditableStatusButtons = document.querySelectorAll('.action_changeEditableStatus input');
        for (let changeSharedEditableStatusButton of changeSharedEditableStatusButtons) {
            changeSharedEditableStatusButton.addEventListener("change", (event) => {
                let params = "actionTarget=changeSharedEditableStatus"
                    + "&participant_id=" + event.target.closest("tr").dataset.participant_id
                    + "&can_edit=" + event.target.value
                    + "&share_uid=" + event.target.closest('tr').dataset.share_uid
                    + "&YII_CSRF_TOKEN=" + LS.data.csrfToken;
                let xhttp = new XMLHttpRequest();
                xhttp.open("POST", editValueParticipantPanel, true);
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send(params);
            });
        }

        $('#pageSizeShareParticipantView').on("change", function(){
            $.fn.yiiGridView.update('share_central_participants',{ data:{ pageSizeShareParticipantView: $(this).val() }});
        });
        if($('#export').hasClass('d-none')){
            $('#export').removeClass('d-none');
        }
    },
    importPanel = function(){
        if(!$('#export').hasClass('d-none')){
            $('#export').addClass('d-none');
        }
    },
    blacklistPanel = function(){
        if(!$('#export').hasClass('d-none')){
            $('#export').addClass('d-none');
        }
    },
    /**
     * Modal for sharing checked items, massive action
     * @param {array} participantIds - Array of participant ids
     * @return
     */
    shareMassiveAction = function(participantIds) {
        var data = {
            modalTarget: 'shareparticipant',
            participantIds: participantIds
        };
        runBaseModal(
            openModalParticipantPanel,
            data,
            'action_save_modal_shareparticipant',
            'shareParticipantActiveForm',
            'list_central_participants'
        );
    },

    /**
     * Modal for adding participants to a survey.
     * Used by massive action.
     * @param {array} participantIds - Array of participant ids
     * @return
     */
    addParticipantToSurvey = function(participantIds) {
        var data = {
            modalTarget: 'addToSurvey',
            participant_id: participantIds.join(',')
        };
        runBaseModal(
            openModalParticipantPanel,
            data,
            'action_save_modal_addToSurvey',
            'addToSurveyActiveForm',
            'list_central_participants'
        );
    },

    /**
     * Call server to delete ONE single participant share
     * @param {string} participantId
     * @param {number} shareUid
     * @return
     */
    deleteSingleParticipantShare = function(url) {
        $.ajax({
            url: url,
            method: "POST",
            dataType: 'json',
            success: function(result){
                $.fn.yiiGridView.update('share_central_participants',{});
            }
        });
    },

    /**
     * Bind all JS functions to button clicks
     * @return
     */
    bindButtons = function() {

        $(document).trigger("actions-updated");
        basics();
        switch($('#locator').data('location')){
            case 'participants' : participantPanel(); break;
            case 'attributes'   : attributePanel(); break;
            case 'sharepanel'   : sharePanel(); break;
            case 'import'       : importPanel(); break;
            case 'blacklist'    : blacklistPanel(); break;
        }
        /**
         * @TODO rewrite export
         */
        $('#export').click(function() { onClickExport(true); });

        window.LS.doToolTip();
    };

    return {
        basics: basics,
        runBaseModal: runBaseModal,
        participantPanel: participantPanel,
        attributePanel: attributePanel,
        sharePanel: sharePanel,
        importPanel : importPanel,
        blacklistPanel : blacklistPanel,
        onClickExport: onClickExport,
        bindButtons: bindButtons,
        shareMassiveAction: shareMassiveAction,
        addParticipantToSurvey: addParticipantToSurvey,
        deleteSingleParticipantShare: deleteSingleParticipantShare
    };

})();

/**
 * ?
 */
function rejectParticipantShareAjax(participant_id){
    var runRejectParticipantShareAjax = function(){
        $.ajax({
            url: editValueParticipantPanel,
            data: {participant_id : participant_id, actionTarget: 'rejectShareParticipant'},
            method: "POST",
            dataType: 'json',
            success: function(result){
                window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                $.fn.yiiGridView.update('share_central_participants',{});
            }
        });
    };
    return runRejectParticipantShareAjax;
}

/**
 * ?
 */
function deleteAttributeAjax(attribute_id){
    var runDeleteAttributeAjax = function(){
        $.ajax({
            url: editValueParticipantPanel,
            data: {attribute_id : attribute_id, actionTarget: 'deleteAttribute'},
            method: "POST",
            dataType: 'json',
            success: function (result){
                window.LS.ajaxAlerts(result.success, 'success', {showCloseButton: true});
                $.fn.yiiGridView.update('list_attributes',{});
            }
        })
    }
    return runDeleteAttributeAjax;
}

/**
 * ?
 */
function insertSearchCondition(id, options){
    options.data.searchcondition=$('#searchcondition').val();
    return options;
}
$(document).on('ready  pjax:scriptcomplete', (LS.CPDB.bindButtons));
