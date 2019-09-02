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
            form = $('#pjax-content').find('form');
        }

        if (form.length < 1)
            throw "No form Found this can't be!";

        return form;
    },
    // displayLoadingState = (el) => {
    //     const loadingSpinner = '<i class="fa fa-cog fa-spin lsLoadingStateIndicator"></i>';
    //     $(el).prop('disabled', true).append(loadingSpinner);
    // },
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
                run: function(ev) {
                    ev.preventDefault();
                    const $form = getForm(this);
                    formSubmitting = true;
                    
                    try {
                        for (let instanceName in CKEDITOR.instances) {
                            CKEDITOR.instances[instanceName].updateElement();
                        }
                    } catch(e) { console.ls.log('Seems no CKEDITOR4 is loaded'); }
                    
                    $form.find('[type="submit"]').first().trigger('click');
                },
                on: 'click'
            },
            _checkSaveButton: {
                check: '#save-button',
                run: function(ev) {
                    ev.preventDefault();
                    const $form = getForm(this);

                    formSubmitting = true;

                    try {
                        for (let instanceName in CKEDITOR.instances) {
                            CKEDITOR.instances[instanceName].updateElement();
                        }
                    } catch(e) { console.ls.log('Seems no CKEDITOR4 is loaded'); }
            
                    $form.find('[type="submit"]').first().trigger('click');
                    displayLoadingState(this);
                },
                on: 'click'
            },
            _checkSaveFormButton: {
                check: '#save-form-button',
                run: function(ev) {
                    ev.preventDefault();
                    const
                        formid = '#' + $(this).attr('data-form-id'),
                        $form = $(formid);
                    //alert($form.find('[type="submit"]').attr('id'));
                    $form.find('[type="submit"]').trigger('click');
                    displayLoadingState(this);
                    return false;
                },
                on: 'click'
            },
            _checkSaveAndNewButton: {
                check: '#save-and-new-button',
                run: function(ev) {
                    ev.preventDefault();
                    const $form = getForm(this);

                    formSubmitting = true;
                    $form.append('<input name="saveandnew" value="' + $('#save-and-new-button').attr('href') + '" />');

                    try {
                        for (let instanceName in CKEDITOR.instances) {
                            CKEDITOR.instances[instanceName].updateElement();
                        }
                    } catch(e) { console.ls.log('Seems no CKEDITOR4 is loaded'); }

                    $form.find('[type="submit"]').first().trigger('click');
                    displayLoadingState(this);

                },
                on: 'click'
            },
            _checkSaveAndCloseButton: {
                check: '#save-and-close-button',
                run: function(ev) {
                    ev.preventDefault();
                    const $form = getForm(this);

                    closeAfterSaveInput.val("true");
                    $form.append(closeAfterSaveInput);
                    formSubmitting = true;
                    $form.find('[type="submit"]').first().trigger('click');
                    displayLoadingState(this);
                },
                on: 'click'
            },
            _checkSaveAndCloseFormButton: {
                check: '#save-and-close-form-button',
                run: function(ev) {
                    ev.preventDefault();
                    const formid = '#' + $(this).attr('data-form-id'),
                        $form = $(formid);

                    // Add input to tell us to not redirect
                    // TODO : change that
                    $('<input type="hidden">').attr({
                        name: 'saveandclose',
                        value: '1'
                    }).appendTo($form);


                    $form.find('[type="submit"]').trigger('click');
                    displayLoadingState(this);
                    return false;
                },
                on: 'click'
            },
            _checkSaveAndNewQuestionButton: {
                check: '#save-and-new-question-button',
                run: function(ev) {
                    ev.preventDefault();
                    const $form = getForm(this);
                    formSubmitting = true;
                    $form.append('<input name="saveandnewquestion" value="' + $('#save-and-new-question-button').attr('href') + '" />');

                    try {
                        for (let instanceName in CKEDITOR.instances) {
                            CKEDITOR.instances[instanceName].updateElement();
                        }
                    } catch(e) { console.ls.log('Seems no CKEDITOR4 is loaded'); }

                    $form.find('[type="submit"]').first().trigger('click');
                    displayLoadingState(this);
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
            }
        }
    };
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
    };

};

const saveController = SaveController();

export default saveController;
