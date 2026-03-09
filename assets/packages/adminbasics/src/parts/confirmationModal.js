/**
 * Neccessary methods for the confirmation modal
 */
import LOG from "../components/lslog";

const ConfirmationModal = function(e){
    //////PREGENERATED VARIABLES
    //Define the scope
    const _this = this;
    const actionBtn = document.getElementById("actionBtn");

    //Set default options
    const optionsDefault = {
        onclick     : null,
        href        : null,
        message     : null,
        keepopen    : null,
        postDatas   : null,
        gridid      : null,
        title       : null,
        btnclass    : 'btn-primary',
        btntext     : actionBtn.dataset.actionbtntext,
        "ajax-url"  : null,
        postUrl     : null,
    };

    //////METHODS
    //Parse available options from specific item.data settings, if not available load relatedTarget settings
    const _parseOptions = (e) => {
        Object.keys(optionsDefault).forEach((key) => {
            optionsDefault[key] = $(_this).data(key) || $(e.relatedTarget).data(key) || optionsDefault[key];
        });
        return optionsDefault;
    },
    //Generate a simple link on the ok button
    _basicLink = () => {
        LOG.log('Binding basicLink in notification panel');
        $(_this).find('.btn-ok').attr('href', options.href);
    },
    //Evaluate a function on ok button click
    _onClickFunction = () => {
        LOG.log('Binding onClick-functions in notification panel');
        const onclick_fn = eval(options.onclick);
        if (typeof onclick_fn == 'function') {
            $(_this).find('.btn-ok').off('click');

            $(_this).find('.btn-ok').on('click', function (ev) {
                if (!options.keepopen )
                {
                    $('#confirmation-modal').modal('hide');
                }
                onclick_fn();
            });
            return;
        }
        LOG.error("Confirmation modal: onclick is not a function. Wrap data-onclick content in (function() { ... }).");
        return;
    },
    //Set up an ajax call and regenerate a gridView on ok button click
    _ajaxHandler = () => {
        LOG.log('Binding ajax handler in notification panel');

        $(_this).find('.btn-ok').on('click', function(ev) {
            $.ajax({
                type: "POST",
                url: options['ajax-url'],
                data: options.postDatas,

                success : function(html, statut)
                {
                    $.fn.yiiGridView.update(options.gridid);                   // Update the list
                    $('#confirmation-modal').modal('hide');
                },
                error :  function(html, statut){
                    $('#confirmation-modal .modal-body-text').append(html.responseText);
                }

            });
        });
    },
    _sendPost = () => {
        LOG.log('Binding post handler on confirmation dialog');
        $(_this).find('.btn-ok').on('click', function (ev) {
            window.LS.sendPost(options.postUrl, '',options.postDatas);
        });
    },
    _setTarget = () => {
        //Set up normal href
        if (!!options.href) {
            _basicLink();
            return;
        }
        //Set up a complete function
        if (!!options.onclick) {
            _onClickFunction();
            return;
        }
        //Set up an ajax post
        if (!!options['ajax-url']) {
            _ajaxHandler();
            return;
        }
        //Set up a handler to send a POST request
        if (!!options.postUrl) {
            _sendPost();
            return;
        }
        LOG.error("Confirmation modal: Found neither data-href or data-onclick, nor ajax data.");
    };

    //////RUN BINDINGS
    //Current options object
    const options = _parseOptions(e);
    //Set the message if available
    $(this).find('.modal-body-text').text(options.message);
    //first remove both classes
    $(this).find('.btn-ok').removeClass("btn-primary btn-danger");
    if (options.btnclass !== null) {
        $(this).find('.btn-ok').addClass(options.btnclass);
    }
    $(this).find('.btn-ok').html(options.btntext);
    //change titel

    if (options.title !== null) {
        $(this).find('.modal-title').html(options.title);
    }
    //Run setTarget to determine loading target
    _setTarget();
};

const loadMethods = ()=>{
    LOG.log('ConfirmationModal calling');
    $('#confirmation-modal').on('show.bs.modal', function(e) {
        ConfirmationModal.call(this,e);
    });
};

export default loadMethods;
