/**
 * Simple way to have remotely loaded modals withoud the need to have a perfect markup and replace everything.
 */

var BootstrapRemoteModal = function(presetOptions, templateOptions){
    presetOptions = presetOptions || {};
    templateOptions = templateOptions || {};
    
    "use strict";
    var options = {
        parentElement       : presetOptions.parentElement      || 'body',
        header              : presetOptions.header             || true,
        footer              : presetOptions.footer             || true,
        saveButton          : presetOptions.saveButton         || false,
        closeIcon           : presetOptions.closeIcon          || true,
        modalTitle          : presetOptions.modalTitle         || '',
        remoteLink          : presetOptions.remoteLink         || "",
        fnOnShow            : presetOptions.fnOnShow           || null,
        fnOnShown           : presetOptions.fnOnShown          || null,
        fnOnHide            : presetOptions.fnOnHide           || null,
        fnOnHidden          : presetOptions.fnOnHidden         || null,
        fnOnLoaded          : presetOptions.fnOnLoaded         || null,
        removeOnClose       : presetOptions.removeOnClose      || false,
        parseScriptsOnLoad  : presetOptions.parseScriptsOnLoad || false,
        blocking            : presetOptions.blocking           || false
    }

    var templateStrings = {
        closeIcon   : templateOptions.closeIcon || '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
        closeButton : templateOptions.closeButton || '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>',
        saveButton  : templateOptions.saveButton || '<button type="button" class="btn btn-primary">Save changes</button>'
    };

    //Define all the blocks and combine them by jquery methods
    var outerBlock      = $('<div class="modal fade" tabindex="-1" role="dialog"></div>'),
        innerBlock      = $('<div class="modal-dialog" role="document"></div>'),
        contentBlock    = $('<div class="modal-content"></div>'),
        headerBlock     = $('<div class="modal-header"></div>'),
        headlineBlock   = $('<h4 class="modal-title"></h4>'),
        bodyBlock       = $('<div class="modal-body"></div>'),
        footerBlock     = $('<div class="modal-footer"></div>'),
        closeIcon       = $(templateOptions.closeIcon),
        closeButton     = $(templateOptions.closeButton),
        saveButton      = $(templateOptions.saveButton);

    var modalObject = null;
    
    var 
    convertKebabCase = function(string){
        return string.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    },
    parseOptions = function(){
        var self=this;
        $.each(options, function(key, option){
            options[key] = self.data(convertKebabCase(key)) || options[key];
        });
    },
    bindEvents = function(){
        modalObject.on('show.bs.modal', function(){
            loadRemote();
            try{ options.fnOnShow } catch (e) {}
        });
        modalObject.on('shown.bs.modal', options.fnOnShown);
        modalObject.on('hide.bs.modal', options.fnOnHide);
        modalObject.on('hidden.bs.modal', function(){
            if(options.removeOnClose === true){
                modalObject.find('.modal-body').html(" ");
            }
            try{ options.fnOnHidden } catch (e) {}
        });
        modalObject.on('loaded.ls.remotemodal', options.fnOnLoaded);
    },
    loadRemote = function(){
        var modal_body = modalObject.find('.modal-body')
        $.ajax({
            url : options.remoteLink,
            method: 'GET', 
            success: function(resolve){
                modal_body.html(resolve);
                modalObject.trigger('loaded.ls.remotemodal');
            }
        });
    },
    combineModal = function(){
        var thisContent = contentBlock.clone();
        
        thisContent.append(bodyBlock.clone());

        if(options.header === true){
            var thisHeader = headerBlock.clone();
            headlineBlock.text(options.modalTitle);
            thisHeader.append(closeIcon.clone());
            thisHeader.append(headlineBlock);
            thisContent.prepend(thisHeader);   
        }
        if(options.footer === true){
            var thisFooter = footerBlock.clone();
            thisFooter.append(closeButton.clone());
            
            if(options.saveButton === true)
                thisFooter.append(saveButton.clone());
            
            thisContent.append(thisFooter);   
        }
        modalObject = outerBlock.clone();
        modalObject.append(innerBlock.clone().append(thisContent));
    },
    bindToElement = function(){
        this.on('click.remotemodal', function(){
            modalObject.modal('toggle');    
        });
    }, 
    runPrepare = function(){
        if(this.data('remote-modal-appended') == 'yes') {
            return;
        }
        parseOptions.call(this);

        combineModal();
        modalObject.appendTo($(options.parentElement));
        bindToElement.call(this);
        bindEvents.call(this);
        
        this.data('remote-modal-appended', 'yes');
    };
    
    
    parseOptions.call(this);

    combineModal();
    modalObject.appendTo($(options.parentElement));

    bindToElement.call(this);
    bindEvents.call(this);
};

jQuery.fn.extend({
    remoteModal : BootstrapRemoteModal
});
