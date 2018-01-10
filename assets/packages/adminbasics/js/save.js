var formSubmitting = false;
$(document).on('ready pjax:scriptcomplete', function () {

    /** These buttons are in global settings */
    if ($('#save-form-button').length > 0) {
        $("#save-form-button").on('click', function (ev) {
            ev.preventDefault();
            var formid = '#' + $(this).attr('data-form-id');
            var $form = $(formid);
            //alert($form.find('[type="submit"]').attr('id'));
            $form.find('[type="submit"]').trigger('click');
            return false;
        });
    }
    if ($('#save-and-close-form-button').length > 0) {
        $('#save-and-close-form-button').on('click', function (ev) {
            ev.preventDefault();
            var formid = '#' + $(this).attr('data-form-id');
            $form = $(formid);

            // Add input to tell us to not redirect
            // TODO : change that
            $('<input type="hidden">').attr({
                name: 'saveandclose',
                value: '1'
            }).appendTo($form);


            $form.find('[type="submit"]').trigger('click');
            return false;
        });
    }

    if ($('#save-and-new-question-button').length > 0) {
        $('#save-and-new-question-button').on('click', function (ev) {
            ev.preventDefault();
            var $form = getForm(this);
            formSubmitting = true;
            $form.append('<input name="saveandnewquestion" value="' + $('#save-and-new-question-button').attr('href') + '" />');

            for (var instanceName in CKEDITOR.instances) {
                CKEDITOR.instances[instanceName].updateElement();
            }

            $form.find('[type="submit"]').first().trigger('click');

        });
    }

    // Attach this <input> tag to form to check for closing after save
    var closeAfterSaveInput = $("<input>")
        .attr("type", "hidden")
        .attr("name", "close-after-save");

    /**
     * Helper function for save buttons onclick event
     *
     * @param {object} that - this from calling method
     * @return {object} jQuery DOM form object
     */
    var getForm = function (that) {
        var form;
        if ($(that).attr('data-use-form-id') == 1) {
            formId = '#' + $(that).attr('data-form-to-save');
            form = [$(formId)];
        } else {
            form = $('#pjax-content').find('form');
        }

        if (form.length < 1)
            throw "No form Found this can't be!";

        return form;
    };

    /**
     * NB: This is not used for survey settings save button anymore. Instead,
     * check out file application/views/admin/survey/editLocalSettings_main_view.php,
     * bottom script tag.
     */
    if ($('#save-button').length > 0) {
        $('#save-button').off('.main').on('click.main', function (ev) {
            ev.preventDefault();
            var $form = getForm(this);
            formSubmitting = true;

            for (var instanceName in CKEDITOR.instances) {
                CKEDITOR.instances[instanceName].updateElement();
            }

            $form.find('[type="submit"]').first().trigger('click');
        });
    }

    if ($('#save-and-new-button').length > 0) {
        $('#save-and-new-button').on('click', function (ev) {
            ev.preventDefault();
            var $form = getForm(this);
            formSubmitting = true;
            $form.append('<input name="saveandnew" value="' + $('#save-and-new-button').attr('href') + '" />');

            for (var instanceName in CKEDITOR.instances) {
                CKEDITOR.instances[instanceName].updateElement();
            }

            $form.find('[type="submit"]').first().trigger('click');

        });
    }

    // Save-and-close button
    if ($('#save-and-close-button').length > 0) {
        $('#save-and-close-button').on('click', function (ev) {
            ev.preventDefault();
            var $form = getForm(this);
            closeAfterSaveInput.val("true");
            $form.append(closeAfterSaveInput);
            formSubmitting = true;
            $form.find('[type="submit"]').first().trigger('click');
        });
    }

    if ($('.open-preview').length > 0) {
        $('.open-preview').on('click', function () {
            var frameSrc = $(this).attr("aria-data-url");
            $('#frame-question-preview').attr('src', frameSrc);
            $('#question-preview').modal('show');
        });
    }

    if ($('#advancedquestionsettingswrapper').length > 0) {
        window.questionFunctions = window.questionFunctions || (new QuestionFunctions()) || null;
        window.questionFunctions.updatequestionattributes();
        
    }
});
