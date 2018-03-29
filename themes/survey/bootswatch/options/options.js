
var prepare = function(){
    var deferred = $.Deferred();
    //activate the bootstrap switch for checkboxes
    $('.action_activate_bootstrapswitch').bootstrapSwitch();
    var inheritPossible = ($('#general_inherit_active').length > 0 ) ;
    //get option Object from Template configuration options
    var optionObject = {"general_inherit" : 1}
    var generalInherit = function(){return $('#TemplateConfiguration_options').val() === 'inherit'; };
    var updateFieldSettings = function(){
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            optionObject[$(item).attr('name')] = $(item).val();
            if($(item).attr('type') == 'radio'){
                optionObject[$(item).attr('name')] = $(item).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });
    };

    if(generalInherit()){
        $('#general_inherit_on').prop('checked',true).trigger('change').closest('label').addClass('active');
        $('.action_hide_on_inherit').addClass('hidden');
    } else {
        $('#general_inherit_off').prop('checked',true).trigger('change').closest('label').addClass('active');
    }

    if($('#TemplateConfiguration_options').length>0 && !generalInherit()){

        try{
            optionObject = JSON.parse($('#TemplateConfiguration_options').val());
        } catch(e){ console.log('No valid option field!'); }
    }

    //check if a form exists to parse the simple option
    if($('.action_update_options_string_form').length > 0 ){
        //Update values in the form to the template options
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){

            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];

            if(itemValue == null || itemValue == undefined){
                itemValue = inheritPossible ? 'inherit' : false;
                optionObject[$(item).attr('name')] = itemValue;
            }

            $(item).val(itemValue);

            if($(item).hasClass('selector_image_selector')){
                if($(item).val() == 'inherit'){
                    $('button[data-target="#'+$(item).attr('id')+'"]').prop('disabled',  true);
                } else {
                    $('button[data-target="#'+$(item).attr('id')+'"]').prop('disabled',  false);
                }
            }

        });

        //hotwapping the select fields to the radiobuttons
        $('.selector_radio_childfield').each(function(i, selectorItem){
            $('input[name='+$(selectorItem).data('parent')+']').on('change', function(){
                if($(this).val() == 'on' && $(this).prop('checked') == true){
                    $(selectorItem).prop('disabled', false);
                } else {
                    $(selectorItem).prop('disabled', true);
                }

                if($(selectorItem).hasClass('selector_image_selector')){
                    $('button[data-target="#'+$(selectorItem).attr('id')+'"]').prop('disabled',  $(selectorItem).val() == 'inherit');
                }
            });
        });

        $('.action_update_options_string_form').find('.selector_option_radio_field').each(function(i,item){
            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];
            //if it is a radio selector, check it and propagate the change to bootstrapSwitch
            if(itemValue == null || itemValue == undefined){
                itemValue = inheritPossible ? 'inherit' : 'off';
                optionObject[$(item).attr('name')] = itemValue;
            }

            //if it is a radio selector, check it and propagate the change to bootstrapSwitch
            if($(item).val() == itemValue){
                $(item).prop('checked', true).trigger('change');
                $(item).closest('label').addClass('active');
            }
        });

        //if the save button is clicked write everything into the template option field and send the form
        $('.action_update_options_string_button').on('click', function(evt){
            evt.preventDefault();
            if(generalInherit()){
                $('#TemplateConfiguration_options').val('inherit');
                //and submit the form
                $('#template-options-form').find('button[type=submit]').trigger('click');
            } else {
                var newOptionObject = {};
                //get all values
                $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
                    newOptionObject[$(item).attr('name')] = $(item).val();
                });
                $('.action_update_options_string_form').find('.selector_option_radio_field').each(function(i,item){
                    if($(item).prop('checked'))
                        newOptionObject[$(item).attr('name')] = $(item).val();
                });
                //now write the newly created object to the correspondent field as a json string
                $('#TemplateConfiguration_options').val(JSON.stringify(newOptionObject));
                //and submit the form
                $('#template-options-form').find('button[type=submit]').trigger('click');
            }
        });

        //hotswapping the general inherit
        $('#general_inherit_on').on('change', function(evt){
            $('#TemplateConfiguration_options').val('inherit');
            $('.action_hide_on_inherit').addClass('hidden');
        });
        $('#general_inherit_off').on('change', function(evt){
            $('.action_hide_on_inherit').removeClass('hidden');
            updateFieldSettings();
        });

        //hotswapping the fields
        $('.action_update_options_string_form').find('.selector_option_value_field').on('change', function(evt){

            if($(this).hasClass('selector_image_selector')){
                if($(this).val() == 'inherit'){
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  true);
                } else {
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  false);
                }
            }

            optionObject[$(this).attr('name')] = $(this).val();
            if($(this).attr('type') == 'radio'){
                optionObject[$(this).attr('name')] = $(this).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });

        //hotswapping the radio fields
        $('.action_update_options_string_form').find('.selector_option_radio_field').on('change', function(evt){
            $(this).prop('checked',true);
            optionObject[$(this).attr('name')] = $(this).val();
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });


        //Bootstwatch theming
        if($('#simple_edit_cssframework').length>0){
            var currentThemeObject = 'inherit';
            if($('#TemplateConfiguration_cssframework_css').val() !== 'inherit'){
                currentThemeObject = {"replace" : [['css/bootstrap.css','']]};
                try{
                    currentThemeObject = JSON.parse($('#TemplateConfiguration_cssframework_css').val());
                } catch(e){ console.error('No valid css framework theme field!'); }

                $('#simple_edit_cssframework').val(currentThemeObject.replace[0][1]);
            }


            $('#simple_edit_cssframework').on('change', function(evt){
                if($('#simple_edit_cssframework').val() === 'inherit'){
                    $('#TemplateConfiguration_cssframework_css').val('inherit');
                } else {
                    currentThemeObject = {};
                    currentThemeObject.replace = [["css/bootstrap.css",$('#simple_edit_cssframework').val()]];
                    $('#TemplateConfiguration_cssframework_css').val(JSON.stringify(currentThemeObject));
                }

            })
        }

    }
    setTimeout(function(){deferred.resolve()},650);

    return deferred.promise();
};



$(document).off('pjax:scriptcomplete.templateOptions').on('ready pjax:scriptcomplete.templateOptions',function(){
    $('.simple-template-edit-loading').css('display','block');
    prepare().then(function(runsesolve){
        $('.simple-template-edit-loading').css('display','none');
    });

    $('.selector__open_lightbox').on('click', function(e){
        e.preventDefault();
        var imgSrc = $($(this).data('target')).find('option:selected').data('lightbox-src');
        var imgTitle = $($(this).data('target')).val();
        imgTitle = imgTitle.split('/').pop();
        if(imgTitle !== 'inherit'){
            $('#lightbox-modal').find('.selector__title').text(imgTitle);
            $('#lightbox-modal').find('.selector__image').attr({'src' : imgSrc, 'alt': imgTitle});
        }
        $('#lightbox-modal').modal('show');
    });

    var uploadImageBind = new bindUpload({
        form: '#upload_frontend',
        input: '#upload_image_frontend',
        progress: '#upload_progress_frontend',
        onBeforeSend : function(){
            $('.simple-template-edit-loading').css('display','block');
        },
        onSuccess : function(){
            setTimeout(function(){$(document).trigger('pjax:load', {url : window.location.href});}, 2500);
        }
    });
});
