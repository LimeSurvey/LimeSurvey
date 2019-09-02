const ConfirmDeleteModal = function (options) {
    const $item = $(this);

    options.fnOnShown = options.fnOnShown || function () {};
    options.fnOnHide = options.fnOnHide || function () {};
    options.removeOnClose = options.removeOnClose || function () {};
    options.fnOnHidden = options.fnOnHidden || function () {};
    options.fnOnLoaded = options.fnOnLoaded || function () {};

    const postUrl = options.postUrl || $item.attr('href'),
        confirmText = options.confirmText || $item.data('text') || '',
        confirmTitle = options.confirmTitle || $item.attr('title') || '',
        postObject = options.postObject || $item.data('post'),
        showTextArea = options.showTextArea || $item.data('show-text-area') || '',
        useAjax = options.useAjax || $item.data('use-ajax') || '',
        keepopen = options.keepopen || $item.data('keepopen') || '',
        gridReload = options.gridReload || $item.data('grid-reload') || '',
        gridid = options.gridid || $item.data('grid-id') || '',
        buttonNo = options.buttonNo || $item.data('button-no') || '<i class="fa fa-times"></i>',
        buttonYes = options.buttonYes || $item.data('button-yes') || '<i class="fa fa-check"></i>',
        parentElement = options.parentElement || $item.data('parent-element') || 'body';

    const closeIconHTML = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
        closeButtonHTML = '<button type="button" class="btn btn-default" data-dismiss="modal">' + buttonNo + '</button>',
        confirmButtonHTML = '<button type="button" class="btn btn-primary selector--button-confirm">' + buttonYes + '</button>';


    //Define all the blocks and combine them by jquery methods
    const outerBlock = $('<div class="modal fade" tabindex="-1" role="dialog"></div>'),
        innerBlock = $('<div class="modal-dialog" role="document"></div>'),
        contentBlock = $('<div class="modal-content"></div>'),
        headerBlock = $('<div class="modal-header"></div>'),
        headlineBlock = $('<h4 class="modal-title"></h4>'),
        bodyBlock = $('<div class="modal-body"></div>'),
        footerBlock = $('<div class="modal-footer"></div>'),
        closeIcon = $(closeIconHTML),
        closeButton = $(closeButtonHTML),
        confirmButton = $(confirmButtonHTML);

    let modalObject = null;

    const combineModal = () => {
            const thisContent = contentBlock.clone();

            thisContent.append(bodyBlock.clone());

            if (confirmTitle !== '') {
                const thisHeader = headerBlock.clone();
                headlineBlock.text(confirmTitle);
                thisHeader.append(closeIcon.clone());
                thisHeader.append(headlineBlock);
                thisContent.prepend(thisHeader);
            }

            const thisFooter = footerBlock.clone();

            thisFooter.append(closeButton.clone());
            thisFooter.append(confirmButton.clone());
            thisContent.append(thisFooter);

            modalObject = outerBlock.clone();
            modalObject.append(innerBlock.clone().append(thisContent));
        },
        addForm = function () {
            const formObject = $('<form name="' + Math.round(Math.random() * 1000) + '_' + confirmTitle.replace(/[^a-bA-B0-9]/g, '') + '" method="post" action="' + postUrl + '"></form>');
            for (let key in postObject) {
                let type = 'hidden',
                    value = postObject[key],
                    htmlClass = '';

                if (typeof postObject[key] == 'object') {
                    type = postObject[key].type;
                    value = postObject[key].value;
                    htmlClass = postObject[key].class
                }

                formObject.append('<input name="' + key + '" value="' + value + '" type="' + type + '" ' + (htmlClass ? 'class="' + htmlClass + '"' : '') + ' />');
            }

            formObject.append('<input name="' + LS.data.csrfTokenName + '" value="' + LS.data.csrfToken + '" type="hidden" />');
            modalObject.find('.modal-body').append(formObject)
            modalObject.find('.modal-body').append('<p>' + confirmText + '</p>');

            if (showTextArea !== '') {
                modalObject.find('form').append('<textarea id="modalTextArea" name="modalTextArea" ></textarea>');
            }

        },
        runAjaxRequest = function () {
            return LS.ajax({
                url: postUrl,
                type: 'POST',
                data: modalObject.find('form').serialize(),

                // html contains the buttons
                success: function (html, statut) {

                    if (keepopen != 'true') {
                        modalObject.modal('hide'); // $modal.modal('hide');
                    } else {
                        modalObject.find('.modal-body').empty().html(html); // Inject the returned HTML in the modal body
                    }

                    // Reload grid
                    if (gridReload) {
                        $('#' + gridid).yiiGridView('update'); // Update the surveys list
                        setTimeout(function () {
                            $(document).trigger("actions-updated");
                        }, 500); // Raise an event if some widgets inside the modals need some refresh (eg: position widget in question list)
                    }

                    if (html.ajaxHelper) {
                        LS.AjaxHelper.onSuccess(html);
                        return;
                    }

                    if (onSuccess) {
                        var func = new Function(onSuccess);
                        func(html);
                        return;
                    }

                },
                error: function (html, statut) {
                    modalObject.find('.modal-body').empty().html(html.responseText);
                    console.ls.log(html);
                }
            });
        },
        bindEvents = function () {
            modalObject.on('show.bs.modal', function () {
                addForm();
                try {
                    options.fnOnShow
                } catch (e) {}
            });
            modalObject.on('shown.bs.modal', function () {
                var self = this;
                modalObject.find('.selector--button-confirm').on('click', function (e) {
                    e.preventDefault();

                    if (!useAjax) {
                        modalObject.find('form').trigger('submit');
                        modalObject.modal('close');
                    } else {
                        // Ajax request
                        runAjaxRequest();
                    }
                });
                options.fnOnShown.call(this);
            });
            modalObject.on('hide.bs.modal', options.fnOnHide);
            modalObject.on('hidden.bs.modal', function () {
                if (options.removeOnClose === true) {
                    modalObject.find('.modal-body').html(" ");
                }
                try {
                    options.fnOnHidden
                } catch (e) {}
            });
            modalObject.on('loaded.ls.remotemodal', options.fnOnLoaded);
        },
        bindToElement = function () {
            $item.on('click.confirmmodal', function () {
                modalObject.modal('toggle');
            });
        },
        runPrepare = function () {

            if ($item.data('confirm-modal-appended') == 'yes') {
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
    confirmModal: ConfirmDeleteModal
});

export default function confirmDeletemodal() {
    $(document).off('click.confirmModalSelector', 'a.selector--ConfirmModal');
    $(document).on('click.confirmModalSelector', 'a.selector--ConfirmModal', function (e) {
        e.preventDefault();
        $(this).confirmModal({});
        $(this).trigger('click.confirmmodal');
    });
};
