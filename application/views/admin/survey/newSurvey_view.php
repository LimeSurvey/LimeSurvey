<?php
	extract($data);
	Yii::app()->loadHelper('admin/htmleditor');
	PrepareEditorScript(false, $this);
?>
<script type="text/javascript">
    standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
</script>
<div class='header ui-widget-header'><?php $clang->eT("Create, import, or copy survey"); ?></div>
<?php
    $this->render('/admin/survey/subview/tab_view',$data);
    $this->render('/admin/survey/subview/tabGeneralNewSurvey_view',$data);
    $this->render('/admin/survey/subview/tabPresentation_view',$data);
    $this->render('/admin/survey/subview/tabPublication_view',$data);
    $this->render('/admin/survey/subview/tabNotification_view',$data);
    $this->render('/admin/survey/subview/tabTokens_view',$data);
?>

<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />
</form>
<?php
    $this->render('/admin/survey/subview/tabImport_view',$data);
    $this->render('/admin/survey/subview/tabCopy_view',$data);
?>
</div>

<p><button onclick="if (isEmpty(document.getElementById('surveyls_title'), '<?php $clang->eT("Error: You have to enter a title for this survey.", 'js');?>')) { document.getElementById('addnewsurvey').submit();}" class='standardbtn' >
        <?php $clang->eT("Save");?>
    </button>
</p>

