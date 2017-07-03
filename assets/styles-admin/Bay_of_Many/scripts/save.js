var formSubmitting = false;
$(document).ready(function(){

/** These buttons are in global settings */
if ($('#save-form-button').length>0){
    $("#save-form-button").on('click', function(ev){
        ev.preventDefault();
        var formid = '#'+$(this).attr('data-form-id');
        $form = $(formid);
        //alert($form.find('[type="submit"]').attr('id'));
        $form.find('[type="submit"]').trigger('click');
        return false;
    });
}
if ($('#save-and-close-form-button').length>0){
    $('#save-and-close-form-button').on('click', function(ev){
        ev.preventDefault();
        var formid = '#'+$(this).attr('data-form-id');
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
    var $form;
    if($(that).attr('data-use-form-id')==1)
    {
        formId = '#'+$(that).attr('data-form-to-save');
        $form = $(formId);
    }
    else
    {
      $form = $('.side-body').find('form');
    }
    return $form;
};

if ($('#save-button').length > 0){
    $('#save-button').on('click', function(ev)
    {
        ev.preventDefault();
        var $form = getForm(this);
        closeAfterSaveInput.val("false");
        $form.append(closeAfterSaveInput);
        formSubmitting = true;
        $form.find('[type="submit"]').first().trigger('click');
    });
}

// Save-and-close button
if ($('#save-and-close-button').length > 0){
    $('#save-and-close-button').on('click', function(ev)
    {
        ev.preventDefault();
        var $form = getForm(this);
        closeAfterSaveInput.val("true");
        $form.append(closeAfterSaveInput);
        formSubmitting = true;
        $form.find('[type="submit"]').first().trigger('click');
    });
}

if($('.open-preview').length>0){
    $('.open-preview').on('click', function(){
        var frameSrc = $(this).attr("aria-data-url");
        $('#frame-question-preview').attr('src',frameSrc);
        $('#question-preview').modal('show');
    });
}

if ($('#advancedquestionsettingswrapper').length>0){
    updatequestionattributes();
}
});
