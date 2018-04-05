var ConfirmDeleteModal = function(options){
    var $item = $(this);

    options.fnOnShown     = options.fnOnShown     || function(){};
    options.fnOnHide      = options.fnOnHide      || function(){};
    options.removeOnClose = options.removeOnClose || function(){};
    options.fnOnHidden    = options.fnOnHidden    || function(){};
    options.fnOnLoaded    = options.fnOnLoaded    || function(){};

    var postUrl       = options.postUrl       || $item.attr('href'),
        confirmText   = options.confirmText   || $item.data('text')           || '',
        confirmTitle  = options.confirmTitle  || $item.attr('title')          || '',
        postObject    = options.postObject    || $item.data('post'),
        buttonNo      = options.buttonNo      || $item.data('button-no')      || '<i class="fa fa-times"></i>',
        buttonYes     = options.buttonYes     || $item.data('button-yes')     || '<i class="fa fa-check"></i>',
        parentElement = options.parentElement || $item.data('parent-element') || 'body';

    var closeIcon      = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
        closeButton    = '<button type="button" class="btn btn-default" data-dismiss="modal">'+buttonNo+'</button>',
        confirmButton  = '<button type="button" class="btn btn-primary selector--button-confirm">'+buttonYes+'</button>';
    

    //Define all the blocks and combine them by jquery methods
    var outerBlock      = $('<div class="modal fade" tabindex="-1" role="dialog"></div>'),
        innerBlock      = $('<div class="modal-dialog" role="document"></div>'),
        contentBlock    = $('<div class="modal-content"></div>'),
        headerBlock     = $('<div class="modal-header"></div>'),
        headlineBlock   = $('<h4 class="modal-title"></h4>'),
        bodyBlock       = $('<div class="modal-body"></div>'),
        footerBlock     = $('<div class="modal-footer"></div>'),
        closeIcon       = $(closeIcon),
        closeButton     = $(closeButton),
        confirmButton   = $(confirmButton);

    var modalObject = null;

    var combineModal = function(){
        var thisContent = contentBlock.clone();
        
        thisContent.append(bodyBlock.clone());

        if(confirmTitle !== ''){
            var thisHeader = headerBlock.clone();
            headlineBlock.text(confirmTitle);
            thisHeader.append(closeIcon.clone());
            thisHeader.append(headlineBlock);
            thisContent.prepend(thisHeader);   
        }
        
        var thisFooter = footerBlock.clone();
        thisFooter.append(closeButton.clone());
        thisFooter.append(confirmButton.clone());
        thisContent.append(thisFooter);   

        modalObject = outerBlock.clone();
        modalObject.append(innerBlock.clone().append(thisContent));
    }, 
    addForm = function(){
        var formObject = $('<form name="'+Math.round(Math.random()*1000)+'_'+confirmTitle.replace(/[^a-bA-B0-9]/g,'')+'" method="post" action="'+postUrl+'"></form>');
        for(var key in postObject){
            var type = 'hidden';
            var value = postObject[key];
            var htmlClass = '';

            if(typeof postObject[key] == 'object') {
                type = postObject[key].type;
                value = postObject[key].value;
                htmlClass = postObject[key].class
            }

            formObject.append('<input name="'+key+'" value="'+value+'" type="'+type+'" '+(htmlClass ? 'class="'+htmlClass+'"' : '')+ ' />');
        }
        formObject.append('<input name="YII_CSRF_TOKEN" value="'+LS.data.csrfToken+'" type="hidden" />');
        modalObject.find('.modal-body').append(formObject)
        modalObject.find('.modal-body').append('<p>'+confirmText+'</p>');
    },
    bindEvents = function(){
        modalObject.on('show.bs.modal', function(){
            addForm();
            try{ options.fnOnShow } catch (e) {}
        });
        modalObject.on('shown.bs.modal', function(){
            var self = this;
            modalObject.find('.selector--button-confirm').on('click', function(e){
                e.preventDefault();
                modalObject.find('form').trigger('submit');
                modalObject.modal('close');
            });   
            options.fnOnShown.call(this);
        });
        modalObject.on('hide.bs.modal', options.fnOnHide);
        modalObject.on('hidden.bs.modal', function(){
            if(options.removeOnClose === true){
                modalObject.find('.modal-body').html(" ");
            }
            try{ options.fnOnHidden } catch (e) {}
        });
        modalObject.on('loaded.ls.remotemodal', options.fnOnLoaded);
    },
    bindToElement = function(){
        $item.on('click.confirmmodal', function(){
            modalObject.modal('toggle');    
        });
    }, 
    runPrepare = function(){
        if($item.data('confirm-modal-appended') == 'yes') {
            return;
        }
        combineModal();
        modalObject.appendTo($(parentElement));
        bindToElement.call(this);
        bindEvents.call(this);
        
        $item.data('confirm-modal-appended', 'yes');
    };
    
    runPrepare();
};

jQuery.fn.extend({
    confirmModal : ConfirmDeleteModal
});
$(document).on('ready pjax:complete', function(){
    $(document).on('click.confirmModalSelector', 'a.selector--ConfirmModal', function(e){
        e.preventDefault();
        $(this).confirmModal({});
        $(this).trigger('click.confirmmodal');
    });
})
