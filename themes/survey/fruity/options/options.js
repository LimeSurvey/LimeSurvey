
var prepare = function(){
    var deferred = $.Deferred();
    //activate the bootstrap switch for checkboxes
    $('.action_activate_bootstrapswitch').bootstrapSwitch();
    var inheritPossible = ($('#general_inherit_active').length > 0 ) ;
    //get option Object from Template configuration options
    var optionObject = {"general_inherit" : 1};
    //dynamic group to be able to do linking on the fly
    var generalInherit = function(){return $('#TemplateConfiguration_options').val() === 'inherit'; };
    //writes options to json
    var updateFieldSettings = function(){
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            optionObject[$(item).attr('name')] = $(item).val();
            if($(item).attr('type') == 'radio'){
                optionObject[$(item).attr('name')] = $(item).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });
    };

    //Un-/Hide everything on general inherit switch
    if(generalInherit()){
        $('#general_inherit_on').prop('checked',true).trigger('change').closest('label').addClass('active');
        $('.action_hide_on_inherit').addClass('hidden');
    } else {
        $('#general_inherit_off').prop('checked',true).trigger('change').closest('label').addClass('active');
    }

    //get template configuration options
    //the failsave is general_inherit = 1
    if($('#TemplateConfiguration_options').length>0 && !generalInherit()){
        try{
            optionObject = JSON.parse($('#TemplateConfiguration_options').val());
        } catch(e){ console.ls.error('No valid option field!'); }
    }

    //check if a form exists to parse the simple option
    if($('.action_update_options_string_form').length > 0 ){
        //Update values in the form to the template options
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            //Get the item value from the option or define inherit through general inherit
            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];
            //Check the itemValue to be not null and set it on the configuration object if neccesary
            if(itemValue == null || itemValue == undefined){
                itemValue = inheritPossible ? 'inherit' : false;
                optionObject[$(item).attr('name')] = itemValue;
            }
            //Set value to html item
            $(item).val(itemValue);

            //Special code for colorpickers
            if($(item).hasClass('selector__colorpicker-field')){
                if($(item).val() == 'inherit'){
                    item.value = $(item).data('inheritvalue');
                    item.value = 'inherit';
                }
            }
            //special code for image selectors
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
            //if the parent changes disable this
            $('input[name='+$(selectorItem).data('parent')+']').on('change', function(){
                if($(this).val() == 'on' && $(this).prop('checked') == true){
                    $(selectorItem).prop('disabled', false);
                } else {
                    $(selectorItem).prop('disabled', true);
                    $(selectorItem).val((inheritPossible ? 'inherit' : ''))
                }
                //if this is an image selector disable the preview button
                if($(selectorItem).hasClass('selector_image_selector')){
                    $('button[data-target="#'+$(selectorItem).attr('id')+'"]').prop('disabled',  $(selectorItem).val() == 'inherit');
                }
            });
        });
        //Check all radio fields
        $('.action_update_options_string_form').find('.selector_option_radio_field').each(function(i,item){
            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];
            //this item can never have no value so either set it to off or inherit
            if(itemValue == null || itemValue == undefined){
                itemValue = inheritPossible ? 'inherit' : 'off';
                optionObject[$(item).attr('name')] = itemValue;
            }

            //propagate the change to bootstrapSwitch
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
                //$('#template-options-form').find('button[type=submit]').trigger('click');
            } else {
                var newOptionObject = {};
                //get all values
                $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){

                    //Special code for numerical
                    if($(item).hasClass('selector-numerical-input')){
                        if(!(/^((\d+)|(inherit))$/.test($(item).val()))){
                            $(item).val((inheritPossible ? 'inherit' : 1000));
                        }
                    }
                    
                    //disabled items should be inherit or false
                    if($(item).prop('disabled')){
                        $(item).val((inheritPossible ? 'inherit' : false));
                    }
                    
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
            updateFieldSettings();
        });
        $('#general_inherit_off').on('change', function(evt){
            $('.action_hide_on_inherit').removeClass('hidden');
            updateFieldSettings();
        });

        //hotswapping the fields
        $('.action_update_options_string_form').find('.selector_option_value_field').on('change', function(evt){

            //Special code for image selector
            if($(this).hasClass('selector_image_selector')){
                if($(this).val() == 'inherit'){
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  true);
                } else {
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  false);
                }
            }

            //Special code for only numerical fields
            if($(this).hasClass('selector-numerical-input')){
                if(!(/^((\d+)|(inherit))$/.test($(this).val()))){
                    $(this).val('inherit');
                }
            }
            //write to option object 
            optionObject[$(this).attr('name')] = $(this).val();
            if($(this).attr('type') == 'radio'){
                optionObject[$(this).attr('name')] = $(this).prop('checked') ? 'on' : 'off';
            }
            //write the option object to json string
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });

        //hotswapping the radio fields
        $('.action_update_options_string_form').find('.selector_option_radio_field').on('change', function(evt){
            $(this).prop('checked',true);
            optionObject[$(this).attr('name')] = $(this).val();
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });

        //hotswapping the colorpickers and adding the reset functionality
        $('.action_update_options_string_form').find('.selector__colorpicker-field').on('click', function(){
            $(this).attr('type', 'color');
            $(this).val($(this).data('inheritvalue'));
        });
        $('.action_update_options_string_form').find('.selector__colorpicker-field').on('change', function(){
            $(this).closest('.input-group').find('.selector__show-inherit-value').css('background-color', $(this).val());
        });
        $('.action_update_options_string_form').find('.selector__reset-colorfield-to-inherit').on('click', function(e){
            e.preventDefault();
            var colorField = $(this).closest('.input-group').find('.selector__colorpicker-field');
            $(this).closest('.input-group').find('.selector__show-inherit-value').css('background-color', colorField.data('inheritvalue'));
            colorField.attr('type','text').val('inherit');
            optionObject[colorField.attr('name')] = 'inherit';
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });


        // Fruity Theming
        if($('#simple_edit_add_css').length>0){
            var currentThemeObject = 'inherit';

            if($('#TemplateConfiguration_files_css').val() !== 'inherit'){

                currentThemeObject = {"add" : ['css/animate.css','css/ajaxify.css', 'css/variations/sea_green.css', 'css/theme.css']};
                try{
                    currentThemeObject = JSON.parse($('#TemplateConfiguration_files_css').val());
                } catch(e){ console.error('No valid monochrom theme field!'); }

                $('#simple_edit_add_css').val(currentThemeObject.add[2]);
            }


            $('#simple_edit_add_css').on('change', function(evt){
                if($('#simple_edit_add_css').val() === 'inherit'){
                    $('#TemplateConfiguration_files_css').val('inherit');
                } else {

                    currentThemeObject = {};
                    currentThemeObject.add = ['css/animate.css','css/ajaxify.css', $('#simple_edit_add_css').val(), 'css/theme.css'];
                    $('#TemplateConfiguration_files_css').val(JSON.stringify(currentThemeObject));
                }
            })
        }


        // Fruity Fonts
        if($('#simple_edit_font').length>0){
            var currentFontObject = 'inherit';
            optionObject.font = optionObject.font || (inheritPossible ? 'inherit' : 'roboto');

            if( optionObject.font !== 'inherit' ){
                $('#simple_edit_font').val(optionObject.font);
            }

            $('#simple_edit_font').on('change', function(evt){
                if($('#simple_edit_font').val() === 'inherit'){
                    $('#TemplateConfiguration_packages_to_load').val('inherit');
                } else {

                    currentFontObject = {};
                    var selectedFontPackage = $(this).find('option:selected');
                    var packageName         = selectedFontPackage.data('font-package');
                    var formatedPackageName = "font-"+packageName;


                    currentFontObject.add = ["pjax", formatedPackageName ];

                }
                $('#TemplateConfiguration_packages_to_load').val(JSON.stringify(currentFontObject));
            })
        }
    }
    setTimeout(function(){deferred.resolve()},650);

    return deferred.promise();
};



$(document).off('pjax:scriptcomplete.templateOptions').on('ready pjax:scriptcomplete.templateOptions',function(){
    $('.simple-template-edit-loading').css('display','block');
    prepare().then(function(runsesolve){
        $('.simple-template-edit-loading').remove();
    });

    $('.selector__open_lightbox').on('click', function(e){
        e.preventDefault();
        var imgSrc = $($(this).data('target')).find('option:selected').data('lightbox-src');
        var imgTitle = $($(this).data('target')).val();
        if(imgTitle !== 'inherit'){
            $('#lightbox-modal').find('.selector__title').text(imgTitle);
            $('#lightbox-modal').find('.selector__image').attr({'src' : imgSrc, 'alt': imgTitle});
        }
        $('#lightbox-modal').modal('show');
    });

    $('.simple_edit_options_checkicon').on('change', function(){
        $(this).siblings('.selector__checkicon-preview').find('i').html('&#x'+$(this).val()+';');
        if($(this).val()=='inherit'){
            $(this).siblings('.selector__checkicon-preview').find('i').html('&#x'+$(this).siblings('.selector__checkicon-preview').find('i').data('inheritvalue')+';');
        }
    })

    var uploadImageBind = new bindUpload({
        form: '#upload_frontend',
        input: '#upload_image_frontend',
        progress: '#upload_progress_frontend',
        onSuccess : function(){
            $(document).trigger('pjax:load', {url : window.location.href});
        }
    });
});
