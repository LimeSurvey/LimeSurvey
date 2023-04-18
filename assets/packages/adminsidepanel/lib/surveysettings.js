$('#copysurveyform').submit(copysurvey);

function setAdministratorFieldsVisibility(form) {
    var option = form.find("[name=administrator]:checked").val();
    var fieldsContainer = $("#conditional-administrator-fields");
    if (option == "custom") {
        fieldsContainer.show(200);
    } else {
        fieldsContainer.hide(200);
    }
}

$(document).on('click', '[data-copy] :submit', function () {
    $('form :input[value=\'' + $(this).val() + '\']').click();
});
// $(document).on('submit',"#addnewsurvey",function(){
//     $('#addnewsurvey').attr('action',$('#addnewsurvey').attr('action')+location.hash);// Maybe validate before ?
// });
$(document).on('ready  pjax:scriptcomplete', function () {

    $('#template').on('change keyup', function (event) {
        console.ls.log('TEMPLATECHANGE', event);
        templatechange($(this));
    });

    $('[data-copy]').each(function () {
        $(this).html($('#' + $(this).data('copy')).html());
    });

    var jsonUrl = jsonUrl || null;

    $('#tabs').on('tabsactivate', function (event, ui) {
        if (ui.newTab.index() > 4) // Hide on import and copy tab, otherwise show
        {
            $('#btnSave').hide();
        } else {
            $('#btnSave').show();
        }
    });

    // If on "Create survey" form
    if ($('#addnewsurvey')) {
        var form = $('#addnewsurvey');

        // Set initial visibility
        setAdministratorFieldsVisibility(form);

        // Update visibility when 'administrator' option changes
        form.find("[name=administrator]").on('change', function() {
            setAdministratorFieldsVisibility(form);
        });
    }
});

function templatechange($element) {
    $('#preview-image-container').html(
        '<div style="height:200px;" class="ls-flex ls-flex-column align-content-center align-items-center"><i class="ri-loader-2-fill remix-spin remix-3x"></i></div>'
    );
    let templateName = $element.val();
    if (templateName === 'inherit')
    {
        templateName = $element.data('inherit-template-name');
    }
    $.ajax({
        url: $element.data('updateurl'),
        data: {templatename: templateName},
        method: 'POST',
        dataType: 'json',
        success: function (data) {
            $('#preview-image-container').html(data.image);
        },
        error: console.ls.error
    });
}

function copysurvey() {
    let sMessage = '';
    if ($('#copysurveylist').val() == '') {
        sMessage = sMessage + sSelectASurveyMessage;
    }
    if ($('#copysurveyname').val() == '') {
        sMessage = sMessage + '\n\r' + sSelectASurveyName;
    }
    if (sMessage != '') {
        alert(sMessage);
        return false;
    }
}

function in_array(needle, haystack, argStrict) {

    var key = '',
        strict = !!argStrict;

    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}

function guidGenerator() {
    var S4 = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return (S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4());
}
