
var prepare = function(){


    ////////////
    // Interface

    var deferred = $.Deferred();

    //activate the bootstrap switch for checkboxes
    $('.action_activate_bootstrapswitch').bootstrapSwitch();

    // general_inherit_active is not present at global level
    // see: https://github.com/LimeSurvey/LimeSurvey/blob/1cbfa11b081f54763b28364472926b155efea5dc/themes/survey/vanilla/options/options.twig#L71
    var inheritPossible = ($('#general_inherit_active').length > 0 );

    //get option Object from Template configuration options
    var optionObject = {"general_inherit" : 1}

    // #TemplateConfiguration_options is the id of the Options field in advanced option
    var generalInherit = function(){return $('#TemplateConfiguration_options').val() === 'inherit'; };

    // action_update_options_string_form is the form containing the simple options
    // So this function find the selectors in the forum, and pass their values to the advanced options
    var updateFieldSettings = function(){
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
            optionObject[$(item).attr('name')] = $(item).val();
            if($(item).attr('type') == 'radio'){
                optionObject[$(item).attr('name')] = $(item).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });
    };



    //////////////////////////////////////
    // Init: show and set simple options
    // Advanced options ==> simple options

    // First: we need to get the actual values of options to generate the state of the simple option page

    // Show/Hide fields on generalInherit
    // To hide a simple option on generalInherit: just add the class "action_hide_on_inherit" to the rows continaing it
    if (generalInherit()){
        $('#general_inherit_on').prop('checked',true).trigger('change').closest('label').addClass('active');
        $('.action_hide_on_inherit').addClass('hidden');
    } else {
        $('#general_inherit_off').prop('checked',true).trigger('change').closest('label').addClass('active');
    }

    // If no general inherit, then pass the value of the "Options" field in advanced option to the object optionObject
    if($('#TemplateConfiguration_options').length>0 && !generalInherit()){

        try{
            optionObject = JSON.parse($('#TemplateConfiguration_options').val());
        } catch(e){ console.ls ? console.ls.error('No valid option field!') : console.log('No valid option field!'); }
    }


    // action_update_options_string_form is the form containing the simple options
    if($('.action_update_options_string_form').length > 0 ){

        // Update values in the form to the template options
        // selector_option_value_field are the select dropdown (like variations and fonts)
        $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){

            // If general inherit, then the value of the dropdown is inherit, else it's the value defined in advanced options
            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];

            // If anything goes wrong (manual edit or anything else), we make sure it will have a correct value
            if(itemValue == null || itemValue == undefined){
                itemValue = inheritPossible ? 'inherit' : false;
                optionObject[$(item).attr('name')] = itemValue;
            }

            $(item).val(itemValue);

            // Image selectors are disabled on inherit
            if($(item).hasClass('selector_image_selector')){
                if($(item).val() == 'inherit'){
                    $('button[data-target="#'+$(item).attr('id')+'"]').prop('disabled',  true);
                } else {
                    $('button[data-target="#'+$(item).attr('id')+'"]').prop('disabled',  false);
                }
            }

        });

        // hotswapping the select fields to the radiobuttons
        // If an option is set to off, the attached selectors are disabled
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

        // Generate the state of switches (On/Off/Inherit)
        $('.action_update_options_string_form').find('.selector_option_radio_field').each(function(i,item){

            // Inherit if global inherit, else set it to the value defined in advanced options
            var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];

            // If anything goes wrong (manual edit or anything else), we make sure it will have a correct value
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

        // hotswapping the fields
        $('.action_update_options_string_form').find('.selector_option_value_field').on('change', function(evt){

            if($(this).hasClass('selector_image_selector')){
                if($(this).val() == 'inherit'){
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  true);
                } else {
                    $('button[data-target="#'+$(this).attr('id')+'"]').prop('disabled',  false);
                }
            }
        });

        // hotswapping the radio fields
        $('.action_update_options_string_form').find('.selector_option_radio_field').on('change', function(evt){
            $(this).prop('checked',true);
        });

    }


    //////////////////////////////////////
    // Run: update advanced options values
    // simple options  ==> Advanced options



    if($('.action_update_options_string_form').length > 0 ){

        //if the save button is clicked write everything into the template option field and send the form
        $('.action_update_options_string_button').on('click', function(evt){
            evt.preventDefault();

            if(generalInherit()){
                $('#TemplateConfiguration_options').val('inherit');
                $('#template-options-form').find('button[type=submit]').trigger('click'); // submit the form
            } else {

                // newOptionObject collects all the values of the different simple options

                var newOptionObject = {};

                // Get values from select dropdown
                $('.action_update_options_string_form').find('.selector_option_value_field').each(function(i,item){
                    newOptionObject[$(item).attr('name')] = $(item).val();
                });

                // Get values from the radio fields
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

            optionObject[$(this).attr('name')] = $(this).val();
            if($(this).attr('type') == 'radio'){
                optionObject[$(this).attr('name')] = $(this).prop('checked') ? 'on' : 'off';
            }
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });

        //hotswapping the radio fields
        $('.action_update_options_string_form').find('.selector_option_radio_field').on('change', function(evt){
            optionObject[$(this).attr('name')] = $(this).val();
            $('#TemplateConfiguration_options').val(JSON.stringify(optionObject));
        });


        // Fonts
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

    var uploadImageBind = new bindUpload({
        form: '#upload_frontend',
        input: '#upload_image_frontend',
        progress: '#upload_progress_frontend',
        onSuccess : function(){
            $(document).trigger('pjax:load', {url : window.location.href});
        }
    });
});
