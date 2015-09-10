<?php 
    extract($settings_data);
	$count = 0;
	if(isset($scripts))
		echo $scripts;
?>
<?php
    $data = array('aTabTitles'=>$aTabTitles, 'aTabContents'=>$aTabContents, 'has_permissions'=>$has_permissions, 'surveyid'=>$surveyid,'surveyls_language'=>$surveyls_language);
?>

<div class="side-body" id="edit-survey-text-element">
    <div class="row">
        <h3 class="pagetitle"><?php echo gT("Edit survey text elements and settings"); ?></h3>
        <div class="row" style="margin-bottom: 100px">
            <?php echo CHtml::form(array("admin/database/index/updatesurveylocalesettings"), 'post', array('id'=>'globalsetting','name'=>'globalsetting','class'=>'form-horizontal form30')); ?>
            <div class="col-lg-8 content-right">
                <?php $this->renderPartial('/admin/survey/subview/tab_edit_view',$data); ?>
            </div>
            <div class="col-lg-4">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_accordion_container', array('data'=>$settings_data)); ?>
            </div>
          </form>            
        </div>
    </div>
</div>    