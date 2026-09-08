<!--<script type="text/javascript" src="<?php echo Yii::app()->getConfig('sCKEditorURL'); ?>/ckeditor.js"></script>-->
<?php
// Register the editor packages here (not globally) so they are only loaded on pages that use an editor. See bug #19391.
Yii::app()->getClientScript()->registerPackage('ckeditor');
Yii::app()->getClientScript()->registerPackage('ckeditoradditions'); // CKEDITOR in a global scope
Yii::app()->getClientScript()->registerPackage('modaleditor');

// CKEDITOR event handlers. Under PJAX ckeditor.js is loaded asynchronously, so these are deferred
// (see the guard at the end of this script) until CKEDITOR is available. See bug #19391.
$ckeditorHandlers = "
    CKEDITOR.on('dialogDefinition', function (ev) {
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;

        // Remove upload tab from Link and Image dialog as it interferes with
        // CSRF protection and upload can be reached using the browse server tab
        if ( dialogName == 'link')
        {
           // remove Upload tab
           dialogDefinition.removeContents( 'upload' );
        }
        if ( dialogName == 'image')
        {
           // remove Upload tab
           dialogDefinition.removeContents( 'Upload' );
        }
    });
    ";

/**
* @todo This following three JS lines are a hack to keep the most common usage of <br> in ExpressionScript from breaking the expression,
* because the HTML editor will insert linebreaks after every <br>, even if it is inside a ExpressionScript tag {}
* The proper way to fix this would be to merge a plugin like ShowProtected (https://github.com/IGx89/CKEditor-ShowProtected-Plugin)
* with LimeReplacementFields and in general use ProtectSource for ExpressionScript
* See https://stackoverflow.com/questions/2851068/prevent-ckeditor-from-formatting-code-in-source-mode
*/
$ckeditorHandlers .= "CKEDITOR.on('instanceReady', function(event) {
        var textareaId = event.editor.element.getId();
        $('#'+textareaId+'_htmleditor_loader').remove();
        event.editor.dataProcessor.writer.setRules( 'br', { breakAfterOpen: 0 } );
    });
    ";

$script = "
    var sReplacementFieldTitle = '" . gT('Placeholder fields', 'js') . "';
    var sReplacementFieldButton = '" . gT('Insert/edit placeholder field', 'js') . "';
    var sSwitchToolbarFullTitle = '" . gT('Show full toolbar', 'js') . "';
    var sSwitchToolbarBasicTitle = '" . gT('Show basic toolbar', 'js') . "';
    var editorwindowsHash = new Object();

    function find_popup_editor(fieldname)
    {
        var window = null;
        for (var key in editorwindowsHash)
        {
            if (key==fieldname && !editorwindowsHash[key].closed)
            {
                window = editorwindowsHash[key];
                return window;
            }
        }
        return null;
    }

    function start_popup_editor(fieldname, editorurl)
    {
        controlidena = fieldname + '_popupctrlena';
        controliddis = fieldname + '_popupctrldis';
        numwindows = editorwindowsHash.length;
        activepopup = find_popup_editor(fieldname);
        if (activepopup == null)
        {
            var targetField = document.getElementById(fieldname);
            targetField.readOnly=true;
            document.getElementById(controlidena).style.display='none';
            document.getElementById(controliddis).style.display='';
            
            // Override language direction if 'data-contents-dir' attribute is set in the target field
            if (targetField.hasAttribute('data-contents-dir')) {
                var inputLangDirection = targetField.getAttribute('data-contents-dir');
                editorurl = editorurl + '/contdir/' + (inputLangDirection ? inputLangDirection : '');
            }
            popup = window.open(editorurl,'', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=690, height=500');

            editorwindowsHash[fieldname] = popup;
        }
        else
        {
            activepopup.focus();
        }
    }

    function updateCKeditor(fieldname,value)
    {
        var mypopup= editorwindowsHash[fieldname];
        if (mypopup)
        {
            var oMyEditor = mypopup.CKEDITOR.instances['MyTextarea'];
            if (oMyEditor) {oMyEditor.setData(value);}
            mypopup.focus();
        }
        else
        {
            var oMyEditor = CKEDITOR.instances[fieldname];
            oMyEditor.setData(value);
        }
    }

    var ckSettings = {
        language : '" . sTranslateLangCode2CK(Yii::app()->session['adminlang']) . "',
        sid : '" . sanitize_int(App()->request->getQuery('sid', 0)) . "',
        gid : '" . sanitize_int(App()->request->getQuery('gid', 0)) . "',
        qid : '" . sanitize_int(App()->request->getQuery('qid', 0)) . "',
        replacementFieldsPath : '" . $this->createUrl("/limereplacementfields/index") . "',
    }

    // CKEDITOR loads synchronously on a full page load but asynchronously under PJAX,
    // so only register its event handlers once it is actually available. See bug #19391.
    var lsRegisterCKEditorHandlers = function() {
        $ckeditorHandlers
    };
    if (typeof CKEDITOR !== 'undefined') {
        lsRegisterCKEditorHandlers();
    } else {
        jQuery(document).one('pjax:scriptcomplete', lsRegisterCKEditorHandlers);
    }
";

Yii::app()->getClientScript()->registerScript('ckEditorPreparingSettings', $script, LSYii_ClientScript::POS_BEGIN);
