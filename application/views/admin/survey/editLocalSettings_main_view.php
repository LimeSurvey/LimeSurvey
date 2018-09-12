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

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <?php
        // Decide in the right title of the dialog depending on permissions
        $sTitle=gT("Edit survey text elements and settings");
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read') && !Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
        {
            $sTitle=gT("Edit survey text elements");
        }
        elseif (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read') && Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
        {
            $sTitle=gT("Edit survey settings");
        }
        $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=>$sTitle)); ?>
        <h3 class="pagetitle"><?php echo $sTitle; ?></h3>

        <!-- Edition container -->

        <!-- Form -->
        <div class="col-xs-12">
            <?php echo CHtml::form(array("admin/database/index/updatesurveylocalesettings"), 'post', array('id'=>'globalsetting','name'=>'globalsetting','class'=>'form-horizontal form30')); ?>
            <div class="row">

                <!-- text edition -->
                <?php
                if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'read'))
                {?>
                    <div class="col-sm-12 col-md-7 content-right">
                        <?php $this->renderPartial('/admin/survey/subview/tab_edit_view',$data); ?>
                    </div>
                    <?php
                }
                ?>

                <!-- settings -->
                <div class="col-sm-12 col-md-5" id="accordion-container" style="background-color: #fff;">
                    <?php
                    if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'read'))
                    {
                        $this->renderPartial('/admin/survey/subview/accordion/_accordion_container', array('data'=>$settings_data));
                    }
                    ?>
                </div>
            </div>

            <!--
            This hidden button is now necessary to save the form.
            Before, there where several nested forms in Global settings, which is invalid in html
            The submit button from the "import ressources" was submitting the whole form.
            Now, the "import ressources" is outside the global form, in a modal ( subview/import_ressources_modal.php)
            So the globalsetting form needs its own submit button
            -->
            <input type="hidden" name="action" value="updatesurveylocalesettings" />
            <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" />
            <input type="hidden" name="language" value="<?php echo $surveyls_language; ?>" />
            <input type='submit' class="hide" id="globalsetting_submit" />
            </form>
        </div>

        <?php $this->renderPartial('/admin/survey/subview/import_ressources_modal', $settings_data); ?>
    </div>
</div>
