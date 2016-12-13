//Namespacing all Methods of the participant panel in one JS-Prototype
LS = LS || {};
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
    runBaseModal = function(url, data, actionButtonClass, formId, gridViewId){

        /**
         * @param {object} result
         * @todo
         */
        var secondSuccess = function(result) {
            $(baseModal).modal('hide');
            $.fn.yiiGridView.update(gridViewId,{});
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
                LS.ajax({
                    url: action,
                    data: formData,
                    method: 'POST',
                    success: secondSuccess,
                    error : function() {
                        console.log(arguments);
                    }
                });
            });
        };


        return LS.ajax({
            url: url, 
            data: data,
            method: 'POST',
            success: firstSuccess,
            error: console.log
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
            YII_CSRF_TOKEN : LS.data.csrfToken
        }; 

        if (!all) {
            $('.selector_participantCheckbox:checked').each(function(i,item){
                postdata.selectedParticipant.push($(item).val());
            });
        }

        $.ajax({
            url: exporttocsvcountall,
            data: postdata,
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
                        $(this).find('h4.modal-title').text(count);
                        $(this).find('.exportButton').on('click', function() {
                            var dldata = postdata;
                            var val = $('#attributes').val();
                            if (val) {
                                dldata.attributes = val.join('+');
                            }
                            else {
                                dldata.attributes = '';
                            }
                            var dlForm = $("<form></form>")
                                .attr('action', exportToCSVURL)
                                .attr('method', "POST");
                            $.each(dldata, function(key,value){
                                $('<input />')
                                    .attr('name', key)
                                    .attr('value', value)
                                    .appendTo(dlForm);
                            });
                            dlForm.submit();
                            $(self).modal("hide");
                        });
                        $('#attributes')
                            .multiselect({ 
                                includeSelectAllOption:true, 
                                selectAllValue: '0',
                                selectAllText: sSelectAllText,
                                nonSelectedText: sNonSelectedText,
                                nSelectedText: sNSelectedText,
                                maxHeight: 140 
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
                var inputs = '<input type="hidden" name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'">';
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
            var data = {modalTarget: 'editparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel, 
                data,
                'action_save_modal_editParticipant',
                'editPartcipantActiveForm', 
                'list_central_participants' 
            ).done(function() {
                var val = $('#participantPanel_edit_modal .ls-bootstrap-switch').attr('checked');
                $('.ls-bootstrap-switch').bootstrapSwitch('state', val == 'checked');
            });
        });

        $('.action_participant_deleteModal').on('click', function(e) {
            e.preventDefault();
            var data = {modalTarget: 'showdeleteparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                    openModalParticipantPanel, 
                    data,
                    'action_save_modal_deleteParticipant',
                    'deleteParticipantActiveForm', 
                    'list_central_participants' 
                    );
        });
        $('.action_participant_infoModal').on('click', function(e) {
            e.preventDefault();
            var data = {
                modalTarget: 'showparticipantsurveys',
                participant_id: $(this).closest('tr').data('participant_id')
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
            var data = {modalTarget: 'shareparticipant', 'participant_id' : $(this).closest('tr').data('participant_id')};
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel,
                data,
                'action_save_modal_shareparticipant',
                'shareParticipantActiveForm',
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
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
                'list_central_participants'
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
        });

        /**
         * Small icon, add participant to a survey
         */
        $('.action_participant_addToSurvey').on('click', function(e) {
            var data = {
                modalTarget: 'addToSurvey',
                participant_id: $(this).closest('tr').data('participant_id')
            };
            //url, data, idString, actionButtonClass, formId, gridViewId
            runBaseModal(
                openModalParticipantPanel, 
                data,
                'action_save_modal_addToSurvey',
                'addToSurveyActiveForm', 
                'list_central_participants' 
            ).done(function() {
                $('.ls-bootstrap-switch').bootstrapSwitch();
            });
        });

        // Toggle all, participant list
        $('#action_toggleAllParticipant').on('click', function() {
            $('.selector_participantCheckbox').prop('checked',$('#action_toggleAllParticipant').prop('checked'));
        });

        // Toggle all, share panel
        $('#action_toggleAllParticipantShare').on('click', function() {
            $('.selector_participantShareCheckbox').prop('checked', $('#action_toggleAllParticipantShare').prop('checked'));
        });
        
        if(($('#pageSizeParticipantView').val() <= 100) || ($('#pageSizeAttributes').val() <= 100) || ($('#pageSizeShareParticipantView').val() <= 100) ){
            $('.action_changeBlacklistStatus').bootstrapSwitch();
        }

        $('.action_changeBlacklistStatus').on('switchChange.bootstrapSwitch', function(event,state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel, 
                method: "POST",
                data: {actionTarget: 'changeBlacklistStatus', 'participant_id': $(self).closest('tr').data('participant_id'), 'blacklist': state},
                dataType: 'json', 
                success: function(resolve){
                    $(self).prop("checked", (resolve.newValue == "Y"));
                }
            })
        });

        $('#pageSizeParticipantView').on("change", function(){
            $.fn.yiiGridView.update('list_central_participants',{ data:{ pageSizeParticipantView: $(this).val() }});
        });

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
                'list_attributes' 
            ); 
        });
        $('.action_attributeNames_editModal').on('click', function(e){
            e.preventDefault();
            var data = {modalTarget: 'editattribute','attribute_id' : $(this).closest('tr').data('attribute_id')};
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

        $('.action_changeAttributeVisibility').bootstrapSwitch();
        $('.action_changeAttributeVisibility').on('switchChange.bootstrapSwitch', function(event,state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel, 
                method: "POST",
                data: { actionTarget: 'changeAttributeVisibility', 'attribute_id': $(self).closest('tr').data('attribute_id'), 'visible': state},
                dataType: 'json', 
                success: function(resolve){
                    $(self).prop("checked", (resolve.newValue == "Y"));
                }
            })
        });
    },
    //JS-bindings especially for the sharePanel
    sharePanel = function() {
        $('#action_toggleAllParticipant').on('click', function(){
            $('.selector_participantCheckbox').prop('checked', $('#action_toggleAllParticipant').prop('checked'));
        });

        $('.action_changeEditableStatus').bootstrapSwitch();

        $('.action_changeEditableStatus').on('switchChange.bootstrapSwitch', function(event, state){
            var self = this;
            $.ajax({
                url: editValueParticipantPanel, 
                method: "POST",
                data: {actionTarget: 'changeSharedEditableStatus', 'participant_id': $(self).closest('tr').data('participant_id'), 'can_edit': state},
                dataType: 'json', 
                success: function(resolve){
                    $(self).prop("checked", resolve.newValue);
                }
            })
        });

        $('#pageSizeShareParticipantView').on("change", function(){
            $.fn.yiiGridView.update('share_central_participants',{ data:{ pageSizeShareParticipantView: $(this).val() }});
        });
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
        ).done(function() {
            $('.ls-bootstrap-switch').bootstrapSwitch();
        });
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
        ).done(function() {
            $('.ls-bootstrap-switch').bootstrapSwitch();
        });
    },

    /**
     * Call server to delete ONE single participant share
     * @param {string} participantId
     * @param {number} shareUid
     * @return
     */
    deleteSingleParticipantShare = function(url) {
        LS.ajax({
            url: url,
            method: "GET",
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
        basics();
        switch($('#locator').data('location')){
            case 'participants' : participantPanel(); break;
            case 'attributes' :  attributePanel(); break;
            case 'sharepanel' :  sharePanel(); break;
        }
        
        /**
         * @TODO rewrite export
         */
        $('#export').click(function() { onClickExport(true); });

        doToolTip();
    };

    return {
        basics: basics,
        runBaseModal: runBaseModal,
        participantPanel: participantPanel,
        attributePanel: attributePanel,
        sharePanel: sharePanel,
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
                notifyFader(result.successMessage, 'well-lg bg-primary text-center');
                $.fn.yiiGridView.update('share_central_participants',{});
            }
        })
    }
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
            success: function(result){
                notifyFader(result.successMessage, 'well-lg bg-primary text-center');
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

$(document).ready(LS.CPDB.bindButtons);
