import forEach from 'lodash/forEach';

import LOG from '../components/lslog';

const SaveController = () => {
    
       let formSubmitting = false;

        // Attach this <input> tag to form to check for closing after save
        const closeAfterSaveInput = $("<input>")
            .attr("type", "hidden")
            .attr("name", "close-after-save");
    


    /**
     * Helper function for save buttons onclick event
     *
     * @param {object} that - this from calling method
     * @return {object} jQuery DOM form object
     */
    const getForm = (that) => {
        let form;
        if ($(that).attr('data-use-form-id') == 1) {
            formId = '#' + $(that).attr('data-form-to-save');
            form = [$(formId)];
        } else {
            form = $('#pjax-content').find('form:not(#translatemenu)').first(); // #translatemenu is a first form on survey quick translate page, so we want to skip it
        }

        if (form.length < 1)
            throw "No form Found this can't be!";

        return form;
    },
    isSubmitting = () => formSubmitting,
    displayLoadingState = (el) => {
        if($(el).data('form-id') == 'addnewsurvey') {
            const loadingSpinner = '<i class="fa fa-cog fa-spin lsLoadingStateIndicator"></i>';
            $(el).prop('disabled', true).append(loadingSpinner);
        }
    },
    stopDisplayLoadingState = () => {
        LOG.log('StopLoadingIconAnimation');
        LS.EventBus.$emit('loadingFinished');
        // $('.lsLoadingStateIndicator').each((i,item) => {$(item).remove();});
    },
    //###########PRIVATE
    checks = () => {
        return {
            _checkExportButton: {
                check: '[data-submit-form]',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const $form = getForm(this);
                    formSubmitting = true;
                    
                    if ($form.data('isvuecomponent') == true) {
                        LS.EventBus.$emit('componentFormSubmit', button)
                    } else {
                        $form.find('[type="submit"]').first().trigger('click');
                        displayLoadingState(this);
                    }
                },
                on: 'click'
            },
            _checkSaveButton: {
                check: '#save-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const $form = getForm(this);
                    formSubmitting = true;
                    if ($form.data('isvuecomponent') == true) {
                        LS.EventBus.$emit('componentFormSubmit', button)
                    } else {
                        $form.find('[type="submit"]:not(.ck)').first().trigger('click');
                        displayLoadingState(this);
                    }
                },
                on: 'click'
            },
            _checkSaveFormButton: {
                check: '#save-form-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const
                        formid = '#' + $(this).attr('data-form-id'),
                        $form = $(formid),
                        $firstSubmit = $form.find('[type="submit"]').first();

                    if($firstSubmit.length > 0) {
                        $firstSubmit.trigger('click');
                    } else {
                        $form.submit();
                    }
                    
                    // check if there are any required inputs that are not filled
                    var cntInvalid = 0;
                    var requiredInputs =  $form.find('input,select').filter("[required='required']");
                    requiredInputs.each(function () {
                        if (this.validity.valueMissing == true) {
                            cntInvalid += 1;
                        }
                    });
                    // show loading state only if all required fields are filled, otherwise enable submit button again
                    if (cntInvalid === 0){
                        displayLoadingState(this);
                    } else {
                        $('#save-form-button').removeClass('disabled');
                    }
                    return false;
                },
                on: 'click'
            },
            _checkSaveAndNewButton: {
                check: '#save-and-new-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const $form = getForm(this);

                    formSubmitting = true;
                    $form.append('<input name="saveandnew" value="' + $('#save-and-new-button').attr('href') + '" />');

                    if ($form.data('isvuecomponent') == true) {
                        LS.EventBus.$emit('componentFormSubmit', button)
                    } else {
                        $form.find('[type="submit"]').first().trigger('click');
                        displayLoadingState(this);
                    }

                },
                on: 'click'
            },
            _checkSaveAndCloseButton: {
                check: '#save-and-close-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const $form = getForm(this);

                    closeAfterSaveInput.val("true");
                    $form.append(closeAfterSaveInput);
                    formSubmitting = true;

                    if ($form.data('isvuecomponent') == true) {
                        LS.EventBus.$emit('componentFormSubmit', button)
                    } else {
                        $form.find('[type="submit"]').first().trigger('click');
                        displayLoadingState(this);
                    }
                },
                on: 'click'
            },
            _checkSaveAndCloseFormButton: {
                check: '#save-and-close-form-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const formid = '#' + $(this).attr('data-form-id'),
                        $form = $(formid);

                    // Add input to tell us to not redirect
                    // TODO : change that
                    $('<input type="hidden">').attr({
                        name: 'saveandclose',
                        value: '1'
                    }).appendTo($form);
                    
                    $form.find('[type="submit"]').first().trigger('click');
                    displayLoadingState(this);
                        
                    return false;
                },
                on: 'click'
            },
            _checkSaveAndNewQuestionButton: {
                check: '#save-and-new-question-button',
                run: function(ev, button=null) {
                    ev.preventDefault();
                    const $form = getForm(this);
                    formSubmitting = true;
                    $form.append('<input name="saveandnewquestion" value="' + $('#save-and-new-question-button').attr('href') + '" />');

                    if ($form.data('isvuecomponent') == true) {
                        LS.EventBus.$emit('componentFormSubmit', button)
                    } else {
                        $form.find('[type="submit"]').first().trigger('click');
                        displayLoadingState(this);
                    }
                },
                on: 'click'
            },
            _checkOpenPreview: {
                check: '.open-preview',
                run: function(ev) {
                    const frameSrc = $(this).attr("aria-data-url");
                    $('#frame-question-preview').attr('src', frameSrc);
                    $('#question-preview').modal('show');
                },
                on: 'click'
            },
            _checkStopLoading: {
                check: '#in_survey_common',
                run: function(ev) {
                    stopDisplayLoadingState();
                    formSubmitting = false;
                },
                on: 'lsStopLoading'
            },
            _checkStopLoadingCreateCopyImport: {
                check: '#create-import-copy-survey',
                run: function(ev) {
                    stopDisplayLoadingState();
                    formSubmitting = false;
                },
                on: 'lsStopLoading'
            }
        };

    };
    const stubEvent = {
        isStub: true,
        preventDefault: ()=>{console.ls.log("Stub prevented");}
    }
    //############PUBLIC
    return () => {
        forEach(checks(), (checkItem) => {
            let item = checkItem.check;
            $(document).off(checkItem.on+'.centralsave', item);

            LOG.log('saveBindings', checkItem, $(item));

            if ($(item).length > 0) {
                $(document).on(checkItem.on+'.centralsave', item, checkItem.run);
                LOG.log($(item), 'on', checkItem.on, 'run', checkItem.run);
            }
        });

        LS.EventBus.$off("saveButtonCalled");
        LS.EventBus.$emit("saveButtonFlushed");
        
        LS.EventBus.$on("saveButtonCalled", (button) => {
            if(!isSubmitting()) {
                forEach(checks(), (checkItem) => {
                    if(checkItem.check == '#'+button.id) {
                        checkItem.run(stubEvent, button);
                        formSubmitting = false;
                    }
                });
            }
        });
    };

};

const saveController = SaveController();

export default saveController;
