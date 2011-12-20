<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
    <head>
        <title><?php printf($clang->gT('Editing %s'), $sFieldText); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex, nofollow" />
        <script type="text/javascript" src="<?php echo Yii::app()->getConfig('generalscripts') . 'jquery/jquery.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo Yii::app()->getConfig('sCKEditorURL') . '/ckeditor.js'; ?>"></script>
    </head>

    <body>
        <form method='post' onsubmit='saveChanges=true;'>

            <input type='hidden' name='checksessionbypost' value='<?php echo Yii::app()->session['checksessionpost']; ?>' />
            <script type='text/javascript'>
                <!--
                function closeme()
                {
                    window.onbeforeunload = new Function('var a = 1;');
                    self.close();
                }

                window.onbeforeunload= function (evt) {
                    close_editor();
                    closeme();
                }


                var saveChanges = false;
                $(document).ready(function(){
                    CKEDITOR.on('instanceReady',CKeditor_OnComplete);
                    var oCKeditor = CKEDITOR.replace( 'MyTextarea' ,  { height	: '350',
                        width	: '98%',
                        customConfig : "<?php echo Yii::app()->getConfig('sCKEditorURL') . '/limesurvey-config.js'; ?>",
                        toolbarStartupExpanded : true,
                        ToolbarCanCollapse : false,
                        toolbar : '<?php echo $toolbarname; ?>',
                        LimeReplacementFieldsSID : "<?php echo $iSurveyId; ?>",
                        LimeReplacementFieldsGID : "<?php echo $iGroupId; ?>",
                        LimeReplacementFieldsQID : "<?php echo $iQuestionId; ?>",
                        LimeReplacementFieldsType: "<?php echo $sFieldType; ?>",
                        LimeReplacementFieldsAction: "<?php echo $sAction; ?>",
                        smiley_path: "<?php echo Yii::app()->getConfig('rooturl') . '/upload/images/smiley/msn/'; ?>",
                        <?php echo $htmlformatoption; ?> });
                });

                function CKeditor_OnComplete( evt )
                {
                    var editor = evt.editor;
                    editor.setData(window.opener.document.getElementsByName("<?php echo $sFieldName; ?>")[0].value);
                    editor.execCommand('maximize');
                    window.status='LimeSurvey <?php echo $clang->gT('Editing', 'js') . ' ' . 'javascript_escape(' . $sFieldText . ', true)'; ?>';
                }

                function html_transfert()
                {
                    var oEditor = CKEDITOR.instances['MyTextarea'];

                    <?php
                    if (in_array($sFieldType, array('editanswer', 'addanswer', 'editlabel', 'addlabel')))
                    {
                    ?>

                    var editedtext = oEditor.getData().replace(new RegExp( "\n", "g" ),'');
                    var editedtext = oEditor.getData().replace(new RegExp( "\r", "g" ),'');

                    <?php
                    }
                    else
                    {
                    ?>

                    var editedtext = oEditor.getData('no strip new line'); // adding a parameter avoids stripping \n

                        <?php
                    }
                    ?>

                    window.opener.document.getElementsByName('<?php echo $sFieldName; ?>')[0].value = editedtext;
                }


                function close_editor()
                {
                    html_transfert();

                    window.opener.document.getElementsByName('<?php echo $sFieldName; ?>')[0].readOnly= false;
                    window.opener.document.getElementsByName('<?php echo $sFieldName; ?>')[0].className='htmlinput';
                    window.opener.document.getElementById('<?php echo $sControlIdEna; ?>').style.display='';
                    window.opener.document.getElementById('<?php echo $sControlIdDis; ?>').style.display='none';
                    window.opener.focus();
                    return true;
                }

                //-->
                </script>

                <textarea id='MyTextarea' name='MyTextarea'></textarea>
        </form>
    </body>
</html>
