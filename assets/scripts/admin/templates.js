// based on TTabs from http://interface.eyecon.ro/
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready pjax:scriptcomplete', function(){

    $('.ace:not(.none)').ace({
        'mode' : editorfiletype,
        'toolbarCallback' : createToolbar
    });

    $('.jquery-ace-wrapper').addClass('card');

    const changesAce = document.getElementById('changes__ace');
    if (changesAce) {
        changesAce.style.width = null;
    }
    const editorToolbar = document.getElementById('editor-toolbar');
    if (editorToolbar) {
        editorToolbar.style.width = null;
    }

    const previewSizeButtonIds = ['iphone', 'x640', 'x800', 'x1024', 'full'];
    const previewSizeSettings = {
        iphone: { width: '375px', height: '812px' },
        x640: { width: '640px', height: '480px' },
        x800: { width: '800px', height: '600px' },
        x1024: { width: '1024px', height: '768px' },
        full: { width: '95%', height: '768px' }
    };

    function selectPreviewSize(selectedId, shouldFocus) {
        const settings = previewSizeSettings[selectedId];
        if (!settings) {
            return;
        }

        $('#previewiframe').css({
            width: settings.width,
            height: settings.height
        });

        previewSizeButtonIds.forEach(function (id) {
            const $button = $('#' + id);
            const isSelected = id === selectedId;

            $button.attr('aria-selected', isSelected ? 'true' : 'false');
            $button.attr('tabindex', isSelected ? '0' : '-1');
            $button.toggleClass('active', isSelected);
        });

        if (shouldFocus) {
            $('#' + selectedId).trigger('focus');
        }
    }

    function previewSizeTabKeydown(event) {
        const tab = event.target.closest('#preview-size-tablist [role="tab"]');
        if (!tab) {
            return;
        }

        const key = event.key;
        if (['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(key) === -1) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const currentIndex = previewSizeButtonIds.indexOf(tab.id);
        if (currentIndex < 0) {
            return;
        }

        let nextIndex = currentIndex;
        if (key === 'ArrowLeft') {
            nextIndex = (currentIndex - 1 + previewSizeButtonIds.length) % previewSizeButtonIds.length;
        } else if (key === 'ArrowRight') {
            nextIndex = (currentIndex + 1) % previewSizeButtonIds.length;
        } else if (key === 'Home') {
            nextIndex = 0;
        } else if (key === 'End') {
            nextIndex = previewSizeButtonIds.length - 1;
        }

        selectPreviewSize(previewSizeButtonIds[nextIndex], true);
    }

    function initPreviewSizeTabs() {
        const $tablist = $('#preview-size-tablist');
        if (!$tablist.length) {
            return;
        }

        previewSizeButtonIds.forEach(function (id) {
            $('#' + id).off('click.ls-preview-size').on('click.ls-preview-size', function () {
                selectPreviewSize(id, false);
            });
        });

        document.removeEventListener('keydown', previewSizeTabKeydown, true);
        document.addEventListener('keydown', previewSizeTabKeydown, true);
    }

    initPreviewSizeTabs();
});

// Creates a toolbar.
function createToolbar(element, editor)
{
    element.attr('id', 'editor-toolbar');
    element.css('background-color', '#F0F0F0');
    element.css('padding', '5px');
    element.css('text-align', 'center');

    $('<button/>').text(surveyThemeEditorLanguageData.undo).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('undo', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.redo).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('redo', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.find).attr('type', 'button').addClass('btn btn-outline-secondary me-1').appendTo(element).on('click', function()
    {
        editor.commands.exec('find', editor);
    });
    $('<button/>').text(surveyThemeEditorLanguageData.replace).attr('type', 'button').addClass('btn btn-outline-secondary').appendTo(element).on('click', function()
    {
        editor.commands.exec('replace', editor);
    });
    editor.focus();
}
