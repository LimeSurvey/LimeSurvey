var ModalEditor = function () {
    var modalSelector = "#htmleditor-modal";
    var targetField;
    var modalTitle;
    var oCKeditor;
    var originalContent;
    var fieldType;

    var loaderSpinner = '  <div  id="htmleditor-modal-loader" class="ls-flex ls-flex-column align-items-center align-content-center" style="height: 200px;">';
    loaderSpinner += '    <div class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">';
    loaderSpinner += '      <div class="ls-flex align-content-center align-items-center">';
    loaderSpinner += '        <div class="loader-adminpanel text-center" :class="extraClass">';
    loaderSpinner += '            <div class="contain-pulse animate-pulse">';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '                <div class="square"></div>';
    loaderSpinner += '            </div>';
    loaderSpinner += '          </div>';
    loaderSpinner += '        </div>';
    loaderSpinner += '      </div>';
    loaderSpinner += '    </div>';

    var closeModal = function () {
        $(modalSelector).modal('hide');
    };

    var openModal = function () {
        // Save original modal content to restore when closing
        originalContent = $(modalSelector).html();

        // Set modal's title
        var title = $(document).find('#htmleditor-modal-title');
        if (modalTitle) title.text(modalTitle);

        $('#htmleditor-modal-save').on('click.modal-editor', modalSave);

        $('#htmleditor-modal-textarea').before(loaderSpinner);
        initEditor();
        $(modalSelector).modal('show');
    };

    var initEditor = function() {
        // Load global settings
        settings = ckSettings || {}; 

        ckeConfig = {
            height: '200',
            width:  '98%',
            toolbarStartupExpanded: true,
            ToolbarCanCollapse: false,
            toolbar: settings.toolbar || 'inline',
            LimeReplacementFieldsSID : settings.sid,
            LimeReplacementFieldsGID : settings.gid,
            LimeReplacementFieldsQID : settings.qid,
            //LimeReplacementFieldsType: "",
            //LimeReplacementFieldsAction: "",
            LimeReplacementFieldsPath : settings.replacementFieldsPath,
            language : settings.language,
            // Accessible name of the editing area, e.g. "Title editor. Press ALT+F10 for the toolbar, ALT+0 for help."
            title: getEditorTitle(),
        };

        // Override language direction if 'data-contents-dir' attribute is set in the target field
        if ($(targetField).get(0).hasAttribute('data-contents-dir')) {
            var inputLangDirection = $(targetField).attr('data-contents-dir');
            ckeConfig.contentsLangDirection = inputLangDirection ? inputLangDirection : '';
        }

        oCKeditor = CKEDITOR.replace(
            'htmleditor-modal-textarea',
            ckeConfig
        );
        // Bound to this instance only: a global listener would be added again every time the modal opens
        oCKeditor.on('instanceReady', CKeditor_OnComplete);
    };

    /**
     * Returns the name of the field that is edited: the title given by the button,
     * otherwise the label of the field or the header of its table column.
     */
    var getFieldLabel = function () {
        if (modalTitle) {
            return String(modalTitle);
        }
        var $field = $(targetField);
        var label = $field.attr('aria-label') || $('label[for="' + $field.attr('id') + '"]').first().text();
        if (!label) {
            var $cell = $field.closest('td, th');
            if ($cell.length) {
                label = $cell.closest('table').find('thead th').eq($cell.index()).text();
            }
        }
        return $.trim(label || '').replace(/[:*\s]+$/, '');
    };

    /**
     * Accessible name of the editing area. It names the edited field instead of the id of the
     * textarea, and tells how to reach the toolbar with the keyboard.
     */
    var getEditorTitle = function () {
        var $modal = $(modalSelector);
        var fieldLabel = getFieldLabel();
        var title = fieldLabel && $modal.data('editorTitle')
            ? String($modal.data('editorTitle')).replace('%s', fieldLabel)
            : ($modal.data('editorTitleDefault') || 'Editor');
        var hint = $modal.data('editorToolbarHint');
        return hint ? title + '. ' + hint : title;
    };

    var CKeditor_OnComplete = function(evt) {
        var editor = evt.editor;
        if (editor.name !== 'htmleditor-modal-textarea') {
            return;
        }
        $('#htmleditor-modal-loader').remove();
        editor.setData($(targetField).val());

        // Escape in the toolbar returns to the editing area (handled by CKEditor).
        // It must not reach the modal, which would close and discard the changes.
        editor.container.$.addEventListener('keydown', function (e) {
            if ((e.key === 'Escape' || e.keyCode === 27) && e.target.closest && e.target.closest('.cke_top')) {
                e.stopPropagation();
            }
        });
    };

    var modalSave = function() {
        $('#htmleditor-modal-save').off('click.modal-editor');
        updateTargetField();
        closeModal();
    }

    var updateTargetField = function() {
        var editedtext = '';
        if (['editanswer', 'addanswer', 'editlabel', 'addlabel'].indexOf(fieldType)) {
            editedtext = oCKeditor.getData().replace(new RegExp( "\n", "g" ),'');
        } else {
            editedtext = oCKeditor.getData('no strip new line'); // adding a parameter avoids stripping \n
        }
        $(targetField).val(editedtext);
    }

    var bindButtons = function () {
        $(document).on('click', '.htmleditor--openmodal', function () {
            var targetFieldId = $(this).data('targetFieldId');
            if (!targetFieldId) return; // Don't open the modal if no target field is specified
            targetField = "#" + targetFieldId;

            modalTitle = $(this).data('modalTitle');
            fieldType = $(this).data('fieldType');
            openModal();
        });
    };

    var bindModals = function () {
        $(document).on('hide.bs.modal', modalSelector, function () {
            // Restore original modal content
            $(modalSelector).html(originalContent);
        });

        $(document).on('shown.bs.modal', modalSelector, function () {
            // Modal show event
        });
    };

    $(document).on('ready pjax:scriptcomplete', function () {
        bindButtons();
        bindModals();
    });

    return {
        bindButtons: bindButtons,
        bindModals: bindModals,
    };
};

LS.ModalEditor = LS.ModalEditor || new ModalEditor();
