<?php
	extract($data);
	Yii::app()->loadHelper('admin/htmleditor');
	PrepareEditorScript(false, $this);
?>
<script type="text/javascript">
    standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
</script>
<div class='header ui-widget-header'><?php eT("Create, import, or copy survey"); ?></div>
<?php
    $this->renderPartial('/admin/survey/subview/tab_view',$data);
    $this->renderPartial('/admin/survey/subview/tabGeneralNewSurvey_view',$data);
    $this->renderPartial('/admin/survey/subview/tabPresentation_view',$data);
    $this->renderPartial('/admin/survey/subview/tabPublication_view',$data);
    $this->renderPartial('/admin/survey/subview/tabNotification_view',$data);
    $this->renderPartial('/admin/survey/subview/tabTokens_view',$data);
?>
    <div class="hidden hide" id="submitsurveybutton">
    <p>
        <button type="submit" name="save" value='insertsurvey'><?php eT("Save"); ?></button>
    </p>
    </div>
</form>
<?php
    $this->renderPartial('/admin/survey/subview/tabImport_view',$data);
    $this->renderPartial('/admin/survey/subview/tabCopy_view',$data);
?>
</div>

<div data-copy="submitsurveybutton"></div>
