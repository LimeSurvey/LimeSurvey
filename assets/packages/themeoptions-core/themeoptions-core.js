var ThemeOptions = function () {
    "use strict";
    //////////////////
    // Define necessary globals

    // general_inherit_active is not present at global level
    // see: https://github.com/LimeSurvey/LimeSurvey/blob/1cbfa11b081f54763b28364472926b155efea5dc/themes/survey/vanilla/options/options.twig#L71
    var inheritPossible = ($('#general_inherit_active').length > 0);

    //get option Object from Template configuration options
    var optionObject = {
        "general_inherit": 1
    }

    var optionObjectInheritedValues = JSON.parse($('#optionInheritedValues').val());
    var optionCssFiles = JSON.parse($('#optionCssFiles').val());
    //get the global form
    var globalForm = $('.action_update_options_string_form');


    /////////////////
    // Define methods run on startup

    // #TemplateConfiguration_options is the id of the Options field in advanced option
    // getter for generalInherit
    var generalInherit = function () {
        return $('#TemplateConfiguration_options').val() === 'inherit';
    };

    //parse the options as set in the advanced form
    var parseOptionObject = function () {
        // If no general inherit, then pass the value of the "Options" field in advanced option to the object optionObject
        if ($('#TemplateConfiguration_options').length > 0 && !generalInherit()) {
            try {
                optionObject = JSON.parse($('#TemplateConfiguration_options').val());
            } catch (e) {
                console.ls ? console.ls.error('No valid option field!') : console.log('No valid option field!');
            }
        }
    };

    // Show/Hide fields on generalInherit
    // To hide a simple option on generalInherit: just add the class "action_hide_on_inherit" to the rows continaing it
    var startupGeneralInherit = function () {

        if (!inheritPossible) return false;

        if (generalInherit()) {
            $('#general_inherit_on').prop('checked', true).trigger('change').closest('label').addClass('active');
            // $('.action_hide_on_inherit').addClass('d-none');
            $('.tab_action_hide_on_inherit').addClass('ls-tab-disabled');

        } else {
            $('#general_inherit_off').prop('checked', true).trigger('change').closest('label').addClass('active');
            $('.action_hide_on_inherit_wrapper').addClass('d-none');
            $('.tab_action_hide_on_inherit').removeClass('ls-tab-disabled');
        }
    };

    // So this function find the selectors in the forum, and pass their values to the advanced options
    var updateFieldSettings = function () {

        if ($('#general_inherit_on').prop('checked')) {
            $('#TemplateConfiguration_options').val('inherit');
            return;
        }

        globalForm.find('.selector_option_value_field').each(function (i, item) {
            //disabled items should be inherit or false
            if ($(item).prop('disabled')) {
                $(item).val((inheritPossible ? 'inherit' : false));
            }

            optionObject[$(item).attr('name')] = $(item).val();
        });

        globalForm.find('.selector_image_selector').each(function (i, item) {

            // disable the preview image button if the image
            // selected could not be mapped to one of the images
            // that actually exists within the theme
            const src = $(item).find('option:selected').data('lightbox-src');
            const missing = src === '';
            const itemId = $(item).attr('id');
            const button = $(`button[data-bs-target="#${itemId}"]`);
            button.prop('disabled', missing);

            // add some feedback to the user, mark field invalid
            $(item).toggleClass('is-invalid', missing);
        });

        globalForm.find('.selector_option_radio_field ').each(function (i, item) {
            //disabled items should be inherit or false
            if ($(item).prop('disabled')) {
                $(item).val((inheritPossible ? 'inherit' : false));
            }

            if ($(item).attr('type') == 'radio') {
                if ($(item).prop('checked')) {
                    optionObject[$(item).attr('name')] = $(item).val();
                }
            }

        });

        globalForm.find('.selector_text_option_value_field').each(function (i, item) {
            //disabled items should be inherit or false
            if ($(item).prop('disabled')) {
                $(item).val((inheritPossible ? 'inherit' : false));
            }

            optionObject[$(item).attr('name')] = $(item).val();
        });

        var newOptionObject = $.extend(true, {}, optionObject);
        delete newOptionObject.general_inherit;

        $('#TemplateConfiguration_options').val(JSON.stringify(newOptionObject));
    };

    ///////////////
    // Utility Methods
    // -- small utilities i.g. for images or similar, or very specialized functions

    var applyColorPickerValue = function (item) {
        if ($(item).hasClass('selector__color-picker')) {
            console.ls.log($(item).data('inheritvalue'), $(item).val(), item);
            if ($(item).val() === 'inherit') {
                $(item).closest('.input-group').find('.selector__colorpicker-inherit-value').val($(item).data('inheritvalue')).trigger('change');
            } else {
                $(item).closest('.input-group').find('.selector__colorpicker-inherit-value').val($(item).val()).trigger('change');
            }
        }
    };

    //Special code for numerical
    var parseNumeric = function (item) {
        if ($(item).hasClass('selector-numerical-input')) {
            if (!(/^((\d+)|(inherit))$/.test($(item).val()))) {
                $(item).val((inheritPossible ? 'inherit' : 1000));
            }
        }
    };

    // display inherited options as tooltips
    var showInheritedValue = function () {
        $.each($('#simple_edit_add_css > option'), function (i, option) {
            $.each(optionCssFiles.add, function (i, item) {
                if (option.value === item && $('#simple_edit_add_css option:first').val() === 'inherit') {
                    $('#simple_edit_add_css option:first').text($('#simple_edit_add_css option:first').text() + ' ' + option.text + ']');
                }
            });
        });

        globalForm.find('.selector-numerical-input').each(function (i, item) {
            var element = $(item);
            //element.attr('title', element.attr('title')+optionObjectInheritedValues[$(item).attr('name')]);
            element.tooltip();
        });

    };

    //Parses the option value for an item
    var parseOptionValue = function (item, fallbackValue) {
        if (fallbackValue == undefined) fallbackValue = false;
        // If general inherit, then the value of the dropdown is inherit, else it's the value defined in advanced options
        var itemValue = generalInherit() ? 'inherit' : optionObject[$(item).attr('name')];

        // If anything goes wrong (manual edit or anything else), we make sure it will have a correct value
        if (itemValue == null || itemValue == undefined) {
            itemValue = inheritPossible ? 'inherit' : fallbackValue;
            optionObject[$(item).attr('name')] = itemValue;
        }
        return itemValue;
    };

    //Set value and propagate to bootstrapSwitch
    var setAndPropageteToSwitch = function (item) {
        $(item).prop('checked', true).trigger('change');
        //$(item).closest('label').addClass('active');
    };


    ///////////////
    // Parser methods
    // -- These methods will either parse through existing fields, or set existing fields to their correct values

    // Update values in the form to the template options
    // selector_option_value_field are the select dropdown (like variations and fonts)
    // TODO: This seems to be designed for select fields only, but it is also used for other input types. Should be reviewed.
    var prepareSelectField = function () {
        globalForm.find('.selector_option_value_field').each(function (i, item) {
            var itemValue = parseOptionValue(item);
            $(item).val(itemValue);
            applyColorPickerValue(item);
        });
    };

    // Generate the state of switches (On/Off/Inherit)
    var parseParentSwitchFields = function () {
        globalForm.find('.selector_option_radio_field').each(function (i, item) {

            var itemValue = parseOptionValue(item, 'off');

            //if it is a radio selector, check it and propagate the change to bootstrapSwitch
            if ($(item).val() == itemValue) {
                setAndPropageteToSwitch(item);
            }
        });
    };

    var prepareFontField = function () {
        var currentPackageObject = 'inherit';
        if ($('body').hasClass('fruity_twentythree')) {
            optionObject.font = optionObject.font || (inheritPossible ? 'inherit' : 'ibm-sans');
        } else {
            optionObject.font = optionObject.font || (inheritPossible ? 'inherit' : 'roboto');
        }

        if (optionObject.font !== 'inherit') {
            $('#simple_edit_options_font').val(optionObject.font);
        }
        updateFieldSettings();
    };

    var prepareFruityThemeField = function () {
        var currentThemeObject = 'inherit';
        if ($('#TemplateConfiguration_files_css').val() !== 'inherit' && $('body').hasClass('fruity')) {
            currentThemeObject = {
                "add": ['css/animate.css', 'css/ajaxify.css', 'css/variations/sea_green.css', 'css/theme.css', 'custom.css']
            };

            try {
                currentThemeObject = JSON.parse($('#TemplateConfiguration_files_css').val());
            } catch (e) {
                console.error('No valid monochrom theme field!');
            }

            if (currentThemeObject.add) {
                $('#simple_edit_add_css').val(currentThemeObject.add.filter(function (item, i) {
                    return /^css\/variations\/.*$/.test(item);
                }));
            }
        }

    };

    // Update values of 'text' options in the form
    var prepareTextField = function () {
        globalForm.find('.selector_text_option_value_field').each(function (i, item) {
            var itemValue = parseOptionValue(item, "");
            $(item).val(itemValue);
        });
    };

    // updates the disabled status of a child field
    // based on the parent element
    // NOTE:
    // for font and variations dropdowns, the childfield
    // class is added and the data-parent attr exists,
    // but no parent element exists in the markup
    // so if we actually have a parent element, enable/disable
    // based on that, otherwise we enable by default
    const updateChild = function(parentEl, childEl) {

        let enabled = true;

        if(parentEl.length) {
            const parentOn = $(parentEl).val() === 'on';
            const parentChecked = $(parentEl).prop('checked') === true;
            enabled = parentOn && parentChecked;
        }

        $(childEl).prop('disabled', !enabled);
    };

    // grab the parent for a given child field
    const getParent = function(childEl) {
        const parentName = $(childEl).data('parent');
        const parentEl = $(`input[name=${parentName}]`);
        return parentEl;
    };

    // go through each child field, grab parent, and update disabled status
    const updateAllChildren = function() {
        $('.selector_radio_childfield').each(function (i, childEl) {
            const parentEl = getParent(childEl);
            updateChild(parentEl, childEl);
        });
    };

    ///////////////
    // HotSwap methods
    // -- These methods connect an input directly to the value in the optionsObject

    // Disable dependent inputs when their parents are set to off, or inherit
    const hotSwapParentRadioButtons = function () {

        // for each child field, add a listener for the
        // parent's change and update the child's disabled
        // status accordingly
        // i = element index in list of matches, unused
        $('.selector_radio_childfield').each(function (i, childEl) {
            const parentEl = getParent(childEl);

            parentEl.on('change', function () {
                updateChild(parentEl, childEl);
            });
        });
    };

    // hotswapping the fields
    var hotSwapFields = function () {

        globalForm.find('.selector_option_value_field, .selector_text_option_value_field').on('change', function (evt) {
            updateFieldSettings();
            parseNumeric(this);
        });

        globalForm.find('.selector_option_radio_field').on('change', function (evt) {
            updateFieldSettings();
            parseNumeric(this);
        });

    };

    var hotswapGeneralInherit = function () {
        //hotswapping the general inherit
        $('#general_inherit_on').on('change', function (evt) {
            $('#TemplateConfiguration_files_css').val('inherit');
            $('#TemplateConfiguration_files_js').val('inherit');
            $('#TemplateConfiguration_files_print_css').val('inherit');
            $('#TemplateConfiguration_options').val('inherit');
            $('#TemplateConfiguration_cssframework_name').val('inherit');
            $('#TemplateConfiguration_cssframework_css').val('inherit');
            $('#TemplateConfiguration_cssframework_js').val('inherit');
            $('#TemplateConfiguration_packages_to_load').val('inherit');
            $('.action_hide_on_inherit_wrapper').removeClass('d-none');
            $('.tab_action_hide_on_inherit').addClass('ls-tab-disabled');


        });
        $('#general_inherit_off').on('change', function (evt) {
            $('.action_hide_on_inherit_wrapper').addClass('d-none');
            $('.tab_action_hide_on_inherit').removeClass('ls-tab-disabled');

            updateFieldSettings();
        });
    };

    var hotswapFontField = function () {
        $('#simple_edit_options_font').on('change', function (evt) {
            var currentPackageObject = $('#TemplateConfiguration_packages_to_load').val() !== 'inherit' ?
                JSON.parse($('#TemplateConfiguration_packages_to_load').val()) :
                $(this).data('inheritvalue');

            if (currentPackageObject === 'inherit') {
                currentPackageObject = {add: []};
            }
            if ($('#simple_edit_options_font').val() === 'inherit') {
                $('#TemplateConfiguration_packages_to_load').val('inherit');
            } else {
                var selectedFontPackage = $(this).find('option:selected');
                var packageName = selectedFontPackage.data('font-package');
                var formatedPackageName = 'font-' + packageName;

                var filteredAdd = currentPackageObject.add.filter(function (value, index) {
                    return !(/^font-.*$/.test(String(value)));
                });
                filteredAdd.push(formatedPackageName);
                currentPackageObject.add = filteredAdd;
                $('#TemplateConfiguration_packages_to_load').val(JSON.stringify(currentPackageObject));
            }
        });
    };

    //hotswapping the colorpickers and adding the reset functionality
    var hotswapColorPicker = function () {

        globalForm.find('.selector__colorpicker-inherit-value')
            .on('change', function (e) {
                $(this).closest('.input-group').find('.selector_option_value_field').val($(this).val()).trigger('change');
            });

        globalForm.find('.selector__reset-colorfield-to-inherit').on('click', function (e) {
            e.preventDefault();
            var colorField = $(this).closest('.input-group').find('.selector__color-picker');
            console.ls.log(colorField, colorField.data('inheritvalue'));
            $(this).closest('.input-group').find('.selector__colorpicker-inherit-value').val(colorField.data('inheritvalue')).trigger('change');
            colorField.attr('type', 'text').val('inherit');
            optionObject[colorField.attr('name')] = 'inherit';
            updateFieldSettings();
        });
    };

    var removeVariationsFromField = function (fieldSelector) {
        if ($(fieldSelector).val() === 'inherit') {
            fieldSelector = '#optionCssFiles';
        }
        let currentValue = {};
        try {
            currentValue = JSON.parse($(fieldSelector).val());
        } catch (error) {
            currentValue = {};
        }
        let empty = true;
        ['add', 'replace', 'remove'].forEach(function (action) {
            if (currentValue.hasOwnProperty(action)) {
                currentValue[action] = currentValue[action].filter(function (item) {
                    return !(/^css\/variations\/.*$/.test(item));
                });
                if (currentValue[action].length) {
                    empty = false;
                }
            }
        });
        if (!empty) {
            $(fieldSelector).val(JSON.stringify(currentValue));
        } else {
            $(fieldSelector).val(inheritPossible ? 'inherit' : JSON.stringify({}));
        }
    };

    // update the files_css field to be saved in the database
    var addVariationToField = function (selectedVariation, filesCssId, selectedMode) {
        let filesCss = [];
        try {
            filesCss = JSON.parse($(filesCssId).val());
        } catch (error) {
        }
        if (!filesCss.hasOwnProperty(selectedMode)) {
            filesCss[selectedMode] = [];
        }
        if (selectedMode === 'replace') {
            filesCss[selectedMode].push(selectedVariation);
        } else {
            filesCss[selectedMode].unshift(selectedVariation);
        }
        $(filesCssId).val(JSON.stringify(filesCss));
    };

    var hotswapTheme = function () {
        $('#simple_edit_options_cssframework').on('change', function (evt) {
            let newThemeDataValue = $('option:selected', this).attr('data-value') || false;
            let selectedVariation = newThemeDataValue || $('#simple_edit_options_cssframework').val();
            let selectedMode = $('#simple_edit_options_cssframework').find('option[value=\'' + selectedVariation + '\']').attr('data-mode') || 'add';
            let filesCssId = '#TemplateConfiguration_files_css';
            let filesCss = {};
            try {
                filesCss = JSON.parse($(filesCssId).val());
            } catch (e) {
                filesCss = $(filesCssId).val();
            }
            // load the parent data if set to inherit, so no data gets lost
            if (filesCss.length === 0 || filesCss === 'inherit') {
                $(filesCssId).val($('#optionCssFiles').val());
            }
            removeVariationsFromField(filesCssId);
            removeVariationsFromField('#TemplateConfiguration_cssframework_css');
            if (selectedVariation !== 'inherit') {
                addVariationToField(selectedVariation, filesCssId, selectedMode);
            } else {
                $(filesCssId).val('inherit');
            }
        });
    };

    ///////////////
    // Event methods
    // -- These methods are triggered on events. Please see `bind´ method for more information
    var onSaveButtonClickAction = function (evt) {
        evt.preventDefault();

        if ($('#general_inherit_on').prop('checked')) {
            $('#TemplateConfiguration_options').val('inherit');
            $('#template-options-form').trigger('submit'); // submit the form
        } else {
            updateFieldSettings();
            //Create a copy of the inherent optionObject
            var newOptionObject = $.extend(true, {}, optionObject);
            newOptionObject.generalInherit = null;

            //now write the newly created object to the correspondent field as a json string
            $('#TemplateConfiguration_options').val(JSON.stringify(newOptionObject));
            //and submit the form
            $('#template-options-form').trigger('submit');
        }
    };


    ///////////////
    // Instance methods
    var bind = function () {
        //if the save button is clicked write everything into the template option field and send the form
        $('#theme-options--submit').on('click', onSaveButtonClickAction);

        //Bind the hotwaps
        hotSwapParentRadioButtons();
        hotSwapFields();
        hotswapGeneralInherit();
        hotswapColorPicker();
        hotswapFontField();
        hotswapTheme();
    };

    var run = function () {
        parseOptionObject();

        startupGeneralInherit();

        prepareSelectField();
        prepareTextField();
        parseParentSwitchFields();
        prepareFontField();
        prepareFruityThemeField();
        showInheritedValue();

        bind();

        // set initial disabled status of child fields
        updateAllChildren();
    };

    return run;

};

$(function () {
    var prepare = function () {

        var deferred = $.Deferred();

        var themeOptionStarter = new ThemeOptions();
        themeOptionStarter();

        setTimeout(function () {
            deferred.resolve();
        }, 650);
        return deferred.promise();
    };

    $('.simple-template-edit-loading').css('display', 'block');
    prepare().then(function (runsesolve) {
        $('.simple-template-edit-loading').css('display', 'none');
    });

    $('.selector__open_lightbox').on('click', function (e) {
        e.preventDefault();
        var imgSrc = $($(this).data('bs-target')).find('option:selected').data('lightbox-src');
        var imgTitle = $($(this).data('bs-target')).val();
        imgTitle = imgTitle.split('/').pop();
        $('#lightbox-modal').find('.selector__title').text(imgTitle);
        $('#lightbox-modal').find('.selector__image').attr({
            'src': imgSrc,
            'alt': imgTitle
        });
        $('#lightbox-modal').modal('show');
    });

    $('.simple_edit_options_checkicon').on('change', function () {
        $(this).siblings('.selector__checkicon-preview').find('i').html('&#x' + $(this).val() + ';');
        if ($(this).val() == 'inherit') {
            $(this).siblings('.selector__checkicon-preview').find('i').html('&#x' + $(this).siblings('.selector__checkicon-preview').find('i').data('inheritvalue') + ';');
        }
    });
});
$(function () {
    let selectedTabHash = window.location.hash;
    let selectedTabElement = document.querySelector('[data-bs-target="' + selectedTabHash + '"]');
    if (selectedTabElement !== null) {
        let tab = new bootstrap.Tab(selectedTabElement);
        tab.show();
    }

    var BindUpload = function (options) {
        var $activeForm = $(options.form);
        var $activeInput = $(options.input);
        var $progressBar = $(options.progress);

        var onSuccess = options.onSuccess || function () {
        };
        var onBeforeSend = options.onBeforeSend || function () {
        };

        var progressHandling = function (event) {
            var percent = 0;
            var position = event.loaded || event.position;
            var total = event.total;
            if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
            }
            // update progressbars classes so it fits your code
            $progressBar.css('width', String(percent) + '%');
            $progressBar.find('span.visually-hidden').text(percent + '%');
        };

        $activeInput.on('change', function (e) {
            e.preventDefault();
            var formData = new FormData($activeForm[0]);
            console.log(JSON.stringify(formData));
            // add assoc key values, this will be posts values
            formData.append('file', $activeInput.prop('files')[0]);

            $.ajax({
                type: 'POST',
                url: $activeForm.attr('action'),
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', progressHandling, false);
                    }
                    return myXhr;
                },
                beforeSend: onBeforeSend,
                success: function (data) {
                    console.log(data);
                    if (data.success === true) {
                        LS.LsGlobalNotifier.createAlert(data.message, 'success', {showCloseButton: true});
                        $progressBar.css('width', '0%');
                        $progressBar.find('span.visually-hidden').text('0%');
                        onSuccess(options.input);
                    } else {
                        LS.LsGlobalNotifier.createAlert(data.message, 'danger', {showCloseButton: true});
                        $progressBar.css('width', '0%');
                        $progressBar.find('span.visually-hidden').text('0%');
                    }
                },
                error: function (error) {
                    $progressBar.css('width', '0%');
                    $progressBar.find('span.visually-hidden').text('0%');
                    console.log(error);
                },
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000
            });
        });
        return this;
    };
    new BindUpload({
        form: '#upload_frontend',
        input: '#upload_image',
        progress: '#upload_progress'
    });
    new BindUpload({
        form: '#upload_frontend',
        input: '#upload_image_frontend',
        progress: '#upload_progress_frontend',
        onBeforeSend: function () {
            $('.simple-template-edit-loading').css('display', 'block');
        },
        onSuccess: function (inputId) {
            let url = new URL(window.location.href);
            let inputElement = document.querySelector(inputId);
            if (inputElement !== null) {
                url.hash = inputElement.closest('.CoreThemeOptions--settingsTab') !== null
                    ? inputElement.closest('.CoreThemeOptions--settingsTab').id
                    : '';
            }
            $(document).trigger('pjax:load', {
                url: url.href
            });
        }
    });
});

