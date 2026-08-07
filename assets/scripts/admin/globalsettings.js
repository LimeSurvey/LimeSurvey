// $Id: globalsettings.js 8964 2010-07-20 20:46:47Z anishseth $

// Namespace
var LS = LS || {  onDocumentReady: {} };

$(document).on('ready  pjax:scriptcomplete', function(){
    $("input:radio[id^='emailmethod']").on('change',Emailchange);
    Emailchange();
    $("input:radio[id^='bounceaccounttype']").on('change',BounceChange);
    BounceChange();
    $("#defaultlang").change(defaultLanguageChange);
    $('#btnRemove').click(removeLanguages);
    $('#btnAdd').click(addLanguages);
    $("#frmglobalsettings").submit(UpdateRestrictedLanguages);

    // Intercept save button clicks to validate before the central save controller runs
    $(document).on('click.emailPluginValidation', '#save-form-button, #save-and-close-form-button', function(e) {
        var formId = $(this).attr('data-form-id');
        if (formId === 'frmglobalsettings' && !validateEmailMethodPlugin()) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    var getStorageUrl = '';
    $('#global-settings-calculate-storage').on(
        'click',
        function(ev) {
            ev.preventDefault();
            var url = $('input[name="global-settings-storage-url"]').val();
            LS.AjaxHelper.ajax({
                url: url,
                method: 'GET'
            });
            return false;
        }
    );

    // Code copied from: https://stackoverflow.com/questions/18999501/bootstrap-3-keep-selected-tab-on-page-refresh
    var activeTab = localStorage.getItem('activeTab');
    if (location.hash) {
        $('a[href=\'' + location.hash + '\']').tab('show');
    } else if (activeTab) {
        $('a[href="' + activeTab + '"]').tab('show');
    }
    $('body').on('click', 'a[data-bs-toggle=\'tab\']', function (e) {
        e.preventDefault();
        var tab_name = this.getAttribute('href');
        if (history.pushState) {
            history.pushState(null, null, tab_name);
        }
        else {
            location.hash = tab_name;
        }
        localStorage.setItem('activeTab', tab_name);

        $(this).tab('show');
        return false;
    });
    $(window).on('popstate', function () {
        var anchor = location.hash ||
            $('a[data-bs-toggle=\'tab\']').first().attr('href');
        $('a[href=\'' + anchor + '\']').tab('show');
    });

    bindSendTestEmail();
});

// Add a language to available languages if it was selected as default language
function defaultLanguageChange(ui,evt){
    if ($("#includedLanguages").containsOption($('#defaultlang').val())==false)
    {
        $("#excludedLanguages option[value='"+$('#defaultlang').val()+"']").remove().appendTo('#includedLanguages');
        $("#includedLanguages").sortOptions();
    }
}

function removeLanguages(ui,evt)
{
    // Do not allow to remove the standard language
    if ($("#includedLanguages option[value="+$('#defaultlang').val()+"]:selected").length>0)
    {
        $("#includedLanguages option[value='"+$('#defaultlang').val()+"']").prop("selected", false);
        alert (msgCantRemoveDefaultLanguage);
    }
    var options = $('#includedLanguages option:selected').sort().clone();
    $('#excludedLanguages').append(options);    
    $('#includedLanguages option:selected').remove();
    var options = $("#excludedLanguages option");                    // Collect options         
    options.detach().sort(function(a,b) {               // Detach from select, then Sort
        var at = $(a).text();
        var bt = $(b).text();         
        return (at > bt)?1:((at < bt)?-1:0);            // Tell the sort function how to order
    });
    options.appendTo("#excludedLanguages");      
}

function addLanguages(ui,evt)
{
    var options = $('#excludedLanguages option:selected').sort().clone();
    $('#includedLanguages').append(options);    
    $('#excludedLanguages option:selected').remove();
    var options = $("#includedLanguages option");                    // Collect options         
    options.detach().sort(function(a,b) {               // Detach from select, then Sort
        var at = $(a).text();
        var bt = $(b).text();         
        return (at > bt)?1:((at < bt)?-1:0);            // Tell the sort function how to order
    });
    options.appendTo("#includedLanguages");     
}

function UpdateRestrictedLanguages(){
    aString='';
    $("#includedLanguages option").each(function(){
        aString=aString+' '+$(this).val();
    });
    $('#restrictToLanguages').val($.trim(aString));
}

function Emailchange(ui,evt)
{
    const selectedMethod = $('#emailmethod input:radio:checked').val();

    // Hide all method-specific settings, then show only the relevant ones
    $('.email-method-setting').hide();
    if (selectedMethod === 'smtp') {
        $('.email-method-smtp').show();
    } else if (selectedMethod === 'plugin') {
        $('.email-method-plugin').show();
    }
}

function validateEmailMethodPlugin() {
    var selectedMethod = $('#emailmethod input:radio:checked').val();
    if (selectedMethod === 'plugin' && $('#emailplugin').val() === '') {
        LS.LsGlobalNotifier.createAlert(msgEmailPluginRequired, 'danger', {showCloseButton: true});
        return false;
    }
    return true;
}

function BounceChange(ui,evt)
{
    bounce_disabled=($('#bounceaccounttype input:radio:checked').val()=='off');
    if (bounce_disabled==true) {
        bounce_disabled='disabled';
        $('#bounceencryption label').addClass('disabled');
    }
    else {
        bounce_disabled='';
        $('#bounceencryption label').removeClass('disabled');
    }
    $("#bounceaccounthost").prop('disabled',bounce_disabled);
    $("#bounceaccountuser").prop('disabled',bounce_disabled);
    $("#bounceaccountpass").prop('disabled',bounce_disabled);
    $("#bounceaccountmailbox").prop('disabled',bounce_disabled);
}

function bindSendTestEmail() {
    // Bind "Send Test Email" action
    $(document).on('click', '#sendtestemailbutton', function (event) {
        event.preventDefault();
        var modal = $('#sendtestemail-confirmation-modal');
        var href = $(this).data('href');
        modal.find('.ajaxloader').show();
        modal.find('.modal-content').html('');
        modal.modal('show');
        $.ajax({
            url: href,
            success: function (html) {
                modal.find('.ajaxloader').hide();
                modal.find('.modal-content').html(html);
            }
        });
    });
}
