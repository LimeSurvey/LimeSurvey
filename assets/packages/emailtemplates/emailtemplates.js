// $Id: saved.js 9330 2010-10-24 22:23:56Z c_schmitz $

/**
 * NOTE: After updating this file, generate the "minified" version with:
 * uglifyjs -c -- emailtemplates.js > emailtemplates.min.js
 */

// Namespace
var LS = LS || {  onDocumentReady: {} };

var PrepEmailTemplates = function(){
    var currentTarget = null;

    var kcFinderCallback = function (url)
    {
        if($(currentTarget).closest('.selector__table-container').hasClass('d-none')){
            $(currentTarget).closest('.selector__table-container').removeClass('d-none');
        }
        addAttachment(currentTarget, url);
        window.KCFinder = null;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('kc-modal-open'));
        modal.hide();
    },
    
    /**
     * Edit relevance equation for attachment
     *
     * @param e
     * @return void
     */
    editAttachmentRelevance = function (e)
    {
            /*
            $('#attachment-relevance-editor').on('show.bs.modal', function(event) {
                console.log(event);
                alert('here');
            });
            */
    
            e.preventDefault();
            var target = $(this).parents('tr').find('input.relevance');
            var span = $(this).parents('tr').find('span.relevance');
    
            $('#attachment-relevance-editor textarea').val($(target).val());
    
            $('#attachment-relevance-editor').modal({
                backdrop: 'static',
                keyboard: false
            });
    
            $('#attachment-relevance-editor .btn-primary').one('click', function (event) {
                var newRelevanceEquation = $('#attachment-relevance-editor textarea').val();
                $(target).val(newRelevanceEquation);
    
                if (newRelevanceEquation.length > 50)
                {
                    $(span).html(newRelevanceEquation.replace(/(\r\n|\n|\r)/gm,"").substr(0, 47) + '...');
                }
                else
                {
                    $(span).html(newRelevanceEquation);
                }
    
                $('#attachment-relevance-editor').modal('hide');
            });
    
    },
    
    /**
     * Add an attachment to this template
     *
     * @param target
     * @param url
     * @param relevance
     * @param size
     * @return void
     */
    addAttachment = function (target, url, relevance, size, error)
    {
        if (typeof relevance == 'undefined')
        {
            var relevance = '1';
        }
        if (typeof size == 'undefined')
        {
            var size = '-';
        }
        var filename = decodeURIComponent(url.replace(/^.*[\\\/]/, ''));
    
        var baserow = $('#rowTemplate').find('tbody').html();
    
        if ($(target).is('table'))
        {
            var newrow = $(baserow).clone();
            var templatetype = $(target).attr('data-template');
            var index = $(target).find('tr').length - 1;
    
            if (relevance.length > 50)
            {
                $(newrow).find('span.relevance').html(relevance.replace(/(\r\n|\n|\r)/gm,"").substr(0, 47) + '...');
            }
            else
            {
                $(newrow).find('span.relevance').html(relevance);
            }
    
            $(newrow).find('input.relevance').val(relevance).attr('name', 'attachments' + templatetype + '[' + index + '][relevance]');
            $(newrow).find('input.filename').attr('name', 'attachments' + templatetype + '[' + index + '][url]');
            if (error) {
                $(newrow).find('input.filename').parent().append($("<span class='fa fa-exclamation-triangle text-danger' title='" + error + "'></span>"));
            }
            $(newrow).appendTo($(target).find('tbody'));
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('kc-modal-open'));
            modal.hide();
        }
        else
        {
            var newrow = target;
        }
    
    
        $('.edit-relevance-equation').off('click').on('click', editAttachmentRelevance);
        $('.btnattachmentremove').off('click').on('click', removeAttachment);
    
        $('span.filename').off('click').on('click', function(e) {
            e.preventDefault();
            var target = $(this).parents('tr');
            var ckTarget = $(this).parents('table').data('ck-target');
            uri = LS.data.baseUrl + '/vendor/kcfinder/browse.php?opener=custom&type=files&CKEditor='+ckTarget+'&langCode='+sKCFinderLanguage;
            openKCFinderSingleFile(target, uri);
        });
    
        $(newrow).find('span.filesize').text(formatFileSize(size));
        $(newrow).find('span.filename').text(filename);
        $(newrow).find('input.filename').val(url);
    },
    removeAttachment = function (e)
    {
        e.preventDefault();
        $(this).parents('tr').remove();
    },
    formatFileSize = function (bytes)
    {
        if (bytes >= 1000000)
        {
            return (bytes / 1000000).toFixed(2) + 'MB';
        }
        else if (bytes < 1000000)
        {
            return (bytes / 1000).toFixed(0) + 'KB';
        }
        return bytes;
    },
        openKCFinderSingleFile = function (target, uri) {
            let modalElement = document.getElementById('kc-modal-open');
            let modal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('shown.bs.modal', function () {
                currentTarget = target;
                window.KCFinder = {};
                window.KCFinder.target = target;
                window.KCFinder.callBack = kcFinderCallback;
                $('#kc-modal-open').find('iframe').attr('src', uri);
            }, {once: true});
            modalElement.addEventListener('hidden.bs.modal', function () {
                $(this).find('iframe').attr('src', 'about:blank');
            }, {once: true});
            modal.show();
        },
    bindActions = function(elements, translate, resetUrl){
        $(elements.validate).remoteModal({}, {
            closeIcon : '<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label="'+translate.close+'"></button>',
            closeButton : '<button type=\"button\" class=\"btn btn-outline-secondary\" data-bs-dismiss=\"modal\">'+translate.close+'</button>',
            saveButton : '<button type=\"button\" class=\"btn btn-primary\">'+translate.save+'</button>'
        });
        $(elements.reset).on('click', function(){
            var $self = $(this);
            $.ajax({
                url : resetUrl,
                dataType: 'html',
                success: function(result){
                    if(CKEDITOR !== undefined && CKEDITOR.instances[$self.data('target')]){
                        CKEDITOR.instances[$self.data('target')].setData(result);
                    } else {
                        $('#'+$self.data('target')).val(result);
                    }

                },
                error: console.ls.error,
                beforeSend: function(){
                    if(CKEDITOR !== undefined && CKEDITOR.instances[$self.data('target')]){
                        CKEDITOR.instances[$self.data('target')].setData('');
                    } else {
                        $('#'+$self.data('target')).val('');
                    }

                }
            });
        });
    },

    init = function(modal_id){
        // Binds the Default value buttons for each email template subject and body text
        $('.fillin').off('click').on('click', function(e) {
            e.preventDefault;
            var newval = $(this).attr('data-value');
            var target = $('#' + $(this).attr('data-target'));
            $(target).val(newval);
            try{
                updateCKeditor($(this).attr('data-target'),newval);
            }
            catch(err) {}
        });

        $('button.add-attachment').off('click.emailtemplates').on('click.emailtemplates', function(e) {
            e.preventDefault();
            var target = $($(this).data('target'));
            var ckTarget =  $(this).data('ck-target');
            var uri = LS.data.baseUrl + '/vendor/kcfinder/browse.php?opener=custom&type=files&CKEditor='+ckTarget+'&langCode='+sKCFinderLanguage

            openKCFinderSingleFile(target, uri);

        });
    };

    return {
        init: init,
        bindActions: bindActions,
        currentTarget: currentTarget,
        addAttachment: addAttachment
    };
};


