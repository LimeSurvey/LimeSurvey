<?php
/**
 * General container for edit survey action
 */
?>

<script type="text/javascript">
    standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
</script>

<?php
    extract($settings_data);
    $count = 0;
    if(isset($scripts))
        echo $scripts;
    $data = array('aTabTitles'=>$aTabTitles, 'aTabContents'=>$aTabContents, 'has_permissions'=>$has_permissions, 'surveyid'=>$surveyid,'surveyls_language'=>$surveyls_language);
?>

<div class="side-body" id="edit-survey-text-element">
    <div class="row">
        <h3 class="pagetitle"><?php echo gT("Edit survey text elements and settings"); ?></h3>

        <!-- Edition container -->

            <!-- Form -->
            <div class="col-xs-12">
                <?php echo CHtml::form(array("admin/database/index/updatesurveylocalesettings"), 'post', array('id'=>'globalsetting','name'=>'globalsetting','class'=>'form-horizontal form30')); ?>
                    <div class="row">

                        <!-- text edition -->
                        <div class="col-sm-12 col-md-7 content-right">
                            <?php $this->renderPartial('/admin/survey/subview/tab_edit_view',$data); ?>
                        </div>

                        <!-- settings -->
                        <div class="col-sm-12 col-md-5" id="accordion-container" style="background-color: #fff;">
                            <?php $this->renderPartial('/admin/survey/subview/accordion/_accordion_container', array('data'=>$settings_data)); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

</div>
