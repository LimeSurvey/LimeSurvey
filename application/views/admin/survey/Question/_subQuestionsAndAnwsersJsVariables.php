<?php
/**
 * This subview render the javaScript variables for subQuestion_view and answerOptions_view
 * @var $jsVariableType  Define which type of javascript variables should be render
 */
?>
<script type='text/javascript'>
    // Common variables between subquestions / answers options
    var cancel                  = '<?php eT('Cancel'); ?>';
    var check                   = true;
    var lafail                  = '<?php eT('Sorry, the request failed!'); ?>';
    var lanameurl               = '<?php echo Yii::app()->createUrl('/admin/labels/sa/getAllSets'); ?>';
    var langs                   = '<?php echo implode(';',$anslangs); ?>';
    var languagecount           = <?php echo count($anslangs); ?>;
    var lasaveurl               = '<?php echo Yii::app()->createUrl('/admin/labels/sa/ajaxSets'); ?>';
    var lasuccess               = '<?php eT('The records have been saved successfully!'); ?>';
    var lsbrowsertitle          = '<?php eT('Label set browser','js'); ?>';
    var lsdetailurl             = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetdetails'); ?>';
    var lspickurl               = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetpicker'); ?>';
    var ok                      = '<?php eT('Ok'); ?>';
    var saveaslabletitle        = '<?php eT('Save as label set','js'); ?>';
    var sCheckLabelURL          = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxchecklabel'); ?>';
    var sImageURL               = '<?php echo Yii::app()->getConfig('adminimageurl'); ?>';
    var sLabelSetName           = '<?php eT('Label set name','js'); ?>';
    var strcode                 = '<?php eT('Code','js'); ?>';
    var strlabel                = '<?php eT('Label','js'); ?>';
    var strNoLabelSet           = '<?php eT('There are no label sets which match the survey default language','js'); ?>';
</script>

<?php if ($viewType=='subQuestions'): ?>
    <script>
        // variables with different values in subqestions / answer options
        var newansweroption_text    = '<?php eT('New answer option','js'); ?>';
        var quickaddtitle           = '<?php eT('Quick-add answers','js'); ?>';
        var strCantDeleteLastAnswer = '<?php eT('You cannot delete the last answer option.','js'); ?>';
        var duplicatesubquestioncode = '<?php eT('Error: You are trying to use duplicate subquestion codes.','js'); ?>';
        var clickToExpand           = '<?php eT('Click to expand'); ?>';
    </script>
<?php elseif($viewType=='answerOptions'):?>
    <script>
        // variables with different values in subqestions / answer options
        var newansweroption_text    = '<?php eT('New answer option','js'); ?>';
        var quickaddtitle           = '<?php eT('Quick-add answers','js'); ?>';
        var strCantDeleteLastAnswer = '<?php eT('You cannot delete the last answer option.','js'); ?>';

        // answer options variables
        var assessmentvisible       = <?php echo $assessmentvisible?'true':'false'; ?>;
        var duplicateanswercode     = '<?php eT('Error: You are trying to use duplicate answer codes.','js'); ?>';
        var sAssessmentValue        = '<?php eT('Assessment value','js'); ?>';
        var scalecount              = <?php echo $scalecount; ?>;

    </script>
<?php endif; ?>

<?php echo PrepareEditorScript(true, $this); ?>
