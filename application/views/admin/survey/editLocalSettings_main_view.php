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
$count = 0;
if(isset($scripts))
    echo $scripts;
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <?php
        $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=>$sTitle)); ?>
        <h3 class="pagetitle"><?php echo $pageTitle; ?></h3>

        <!-- Edition container -->

        <!-- Form -->
        <div class="col-xs-12">
            <?php echo CHtml::form(array("admin/database/index/".$panel['action']), 'post', array('id'=>$panel['name'],'name'=>$panel['name'],'class'=>'form-horizontal form30')); ?>

            <div class="row">
                <?php if (Permission::model()->hasSurveyPermission($iSurveyID, $panel['permission'], $panel['permissionGrade'])):?>
                    <div class="<?=$panel['classes']?>">
                        <?php $this->renderPartial($panel['template'],$panel['data']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!--
            This hidden button is now necessary to save the form.
            Before, there where several nested forms in Global settings, which is invalid in html
            The submit button from the "import ressources" was submitting the whole form.
            Now, the "import ressources" is outside the global form, in a modal ( subview/import_ressources_modal.php)
            So the globalsetting form needs its own submit button
            -->
            <input type="hidden" name="action" value="<?=$panel['action']?>" />
            <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" />
            <input type="hidden" name="language" value="<?php echo $surveyls_language; ?>" />
            <input type='submit' class="hide" id="globalsetting_submit" />
            </form>
        </div>

        <?php //$this->renderPartial('/admin/survey/subview/import_ressources_modal', $settings_data); ?>
    </div>
</div>
