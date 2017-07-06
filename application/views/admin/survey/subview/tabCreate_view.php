<?php
/**
* Tab Create content
* This view display the content for the create tab.
*/
?>
<?php
extract($data);
Yii::app()->loadHelper('admin/htmleditor');
PrepareEditorScript(false, $this);
?>
<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>'
    var sURLParameters = '';
    var sAddParam = '';
    var standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    var templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
    var formId = 'addnewsurvey';

</script>
<!-- Form submited by save button menu bar -->
<?php echo CHtml::form(array('admin/survey/sa/insert'), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'form-horizontal')); ?>
    <div class="ls-flex-row align-items-center align-content-center">
        <div class="grow-1 ls-flex-column fill align-items-center align content-center">
            <!-- Previous pane button -->
            <button class="btn" name="navigation_back" id="navigation_back" value="navigation_back"><i class="fa fa-chevron-left" style="font-size:82;"></i></button>
        </div>
        <div class="grow-10 ls-space padding left-10 right-10">
            <ul class="nav nav-tabs" role="tablist" id="create_survey_tablist">
                <li class="active"><a class="create_survey_wizard_tabs" data-count="1" href="#texts" data-toggle="tab"><?=gT("Survey texts")?></a></li>
                <li><a class="create_survey_wizard_tabs" data-count="2" href="#general-settings" data-toggle="tab"><?=gT("General settings")?></a></li>
                <li><a class="create_survey_wizard_tabs" data-count="3" href="#presentation" data-toggle="tab"><?=gT("Presentation & navigation")?></a></li>
                <li><a class="create_survey_wizard_tabs" data-count="4" href="#publication" data-toggle="tab"><?=gT("Publication & access control")?></a></li>
                <li><a class="create_survey_wizard_tabs" data-count="5" href="#data-management" data-toggle="tab"><?=gT("Notification & data management")?></a></li>
                <li><a class="create_survey_wizard_tabs" data-count="6" href="#tokens" data-toggle="tab"><?=gT("Participant settings")?></a></li>
            </ul>
        </div>
        <div class="grow-1 ls-flex-column fill align-items-center align-content-center">
            <!-- Next pane button -->
            <button class="btn" name="navigation_next" id="navigation_next" value="navigation_next"><i class="fa fa-chevron-right" style="font-size:82;"></i></button>
        </div>
    </div>
    <div class="ls-flex-row align-items-center align-content-center">
        <div class="grow-10 ls-space padding left-10 right-10">
            <div class="tab-content">
                <div class="tab-pane active" id="texts" data-count="1">
                    <?php echo $this->renderPartial('/admin/survey/subview/_create_survey_text', $edittextdata); ?>
                </div>
                <div class="tab-pane" id="general-settings" data-count="2">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_generaloptions_panel', $generalsettingsdata); ?>
                </div>
                <div class="tab-pane" id="presentation" data-count="3">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_presentation_panel', $presentationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="publication" data-count="4">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_publication_panel', $publicationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="data-management" data-count="5">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_notification_panel', $notificationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="tokens" data-count="6">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_tokens_panel', $tokensettingsdata); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
            <!-- Submit button -->
            <button class="btn btn-primary btn-success hide" type="submit" name="save" id="create_survey_save_and_send"   value='insertsurvey'><?php eT("Finish & save"); ?></button>
    </div>
</form>

<script>
    $(document).ready(function(){
        sessionStorage.setItem('maxtabs', 1);

        $('#save-form-button').addClass('disabled');
        $('#save-and-close-form-button').addClass('disabled');

        $('#navigation_back').on('click', function(e){
            e.preventDefault();
            $('#create_survey_tablist').find('.active').prev('li').find('a').trigger('click');
        })
        $('#navigation_next').on('click', function(e){
            e.preventDefault();
            $('#create_survey_tablist').find('.active').next('li').find('a').trigger('click');
        })
        $('a.create_survey_wizard_tabs').on('shown.bs.tab', function (e) {
            var count = $(e.target).data('count'); 
            var sessionStorageValue = sessionStorage.getItem('maxtabs') || 1;
            console.log(count, sessionStorageValue);
            if(count>3 || sessionStorageValue>3){
                $('#save-form-button').removeClass('disabled');
                $('#save-and-close-form-button').removeClass('disabled');
            }
        });
        $('#addnewsurvey').on('submit', function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $.ajax({
                url: $('#addnewsurvey').attr('action'),
                data: $('#addnewsurvey').serializeArray(),
                method: 'POST',
                success: function(){console.log(arguments);},
                error: function(){console.log(arguments)},
            });
        })
    });
</script>
