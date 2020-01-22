<?php
/**
* Tab Create content
* This view display the content for the create tab.
 * @var AdminController $this
 * @var Survey $oSurvey
 *
*/
?>
<?php
extract($data);
//Yii::app()->loadHelper('admin/htmleditor');


App()->getClientScript()->registerScript("tabCreate-view-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
    var standardthemerooturl='".Yii::app()->getConfig('standardthemerooturl')."';
    var templaterooturl='".Yii::app()->getConfig('userthemerooturl')."';
    var formId = 'addnewsurvey';
    
", LSYii_ClientScript::POS_BEGIN);
?>
<!-- Form submited by save button menu bar -->
<?php echo CHtml::form(array('admin/survey/sa/insert'), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'')); ?>
    <!-- Submit button, needs to be the first item for the script to take it -->
    <button class="btn btn-primary btn-success hide" type="submit" name="save" id="create_survey_save_and_send"   value='insertsurvey'><?php eT("Finish & save"); ?></button>
    <div class="ls-flex-row align-items-center align-content-center">
        <div class="grow-1 ls-flex-column fill align-items-center align content-center">
            <!-- Previous pane button -->
            <button class="btn btn-default" name="navigation_back" id="navigation_back" value="navigation_back"><i class="fa fa-chevron-left" style="font-size:82;"></i></button>
        </div>
        <div class="grow-10 ls-space padding left-10 right-10">
            <ul class="nav nav-tabs" role="tablist" id="create_survey_tablist">
                <li class="active"><a class="create_survey_wizard_tabs" data-count="1" href="#texts" data-toggle="tab">
                    <i class="fa fa-file-text-o"></i>&nbsp;
                    <?=gT("Text elements")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="2" href="#general-settings" data-toggle="tab">
                    <i class="fa fa-gears"></i>&nbsp;
                    <?=gT("General settings")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="3" href="#datasecurity" data-toggle="tab">
                    <i class="fa fa-shield"></i>&nbsp;
                    <?=gT("Data policy settings")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="4" href="#presentation" data-toggle="tab">
                    <i class="fa fa-eye-slash"></i>&nbsp;
                    <?=gT("Presentation & navigation")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="5" href="#publication" data-toggle="tab">
                    <i class="fa fa-key"></i>&nbsp;
                    <?=gT("Publication & access control")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="6" href="#data-management" data-toggle="tab">
                    <i class="fa fa-feed"></i>&nbsp;
                    <?=gT("Notification & data management")?></a>
                </li>
                <li><a class="create_survey_wizard_tabs" data-count="7" href="#tokens" data-toggle="tab">
                    <i class="fa fa-users"></i>&nbsp;
                    <?=gT("Participant settings")?></a>
                </li>
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
                    <?php echo $this->renderPartial('/admin/survey/subview/tab_edit_view', $edittextdata); ?>
                </div>
                <div class="tab-pane" id="general-settings" data-count="2">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_generaloptions_panel', $generalsettingsdata); ?>
                </div>
                <div class="tab-pane" id="datasecurity" data-count="3">
                    <?php echo $this->renderPartial('/admin/survey/subview/tab_edit_view_datasecurity', $datasecdata); ?>
                </div>
                <div class="tab-pane" id="presentation" data-count="4">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_presentation_panel', $presentationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="publication" data-count="5">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_publication_panel', $publicationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="data-management" data-count="6">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_notification_panel', $notificationsettingsdata); ?>
                </div>
                <div class="tab-pane" id="tokens" data-count="7">
                    <?php echo $this->renderPartial('/admin/survey/subview/accordion/_tokens_panel', $tokensettingsdata); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
            <input type="hidden" name="saveandclose" id="submitaddnesurvey" value="1" />
    </div>
</form>

<script>
    var updateCKfields = function(){
        var curCKEDITOR = window.CKEDITOR === undefined ? null : window.CKEDITOR;
        if(curCKEDITOR !== null) {
            $('textarea').each(function () {
                var $textarea = $(this);
                if(curCKEDITOR.instances[$textarea.attr('name')] != undefined || curCKEDITOR.instances[$textarea.attr('name')] != null) {
                    $textarea.val(curCKEDITOR.instances[$textarea.attr('name')].getData());
                }
            });
        }
    };

    $(document).on('ready pjax:scriptcomplete', function(){
        sessionStorage.setItem('maxtabs', 1);

        $('#navigation_back').on('click', function(e){
            e.preventDefault();
            updateCKfields();
            $('#create_survey_tablist').find('.active').prev('li').find('a').trigger('click');
        })
        $('#navigation_next').on('click', function(e){
            e.preventDefault();
            updateCKfields();
            $('#create_survey_tablist').find('.active').next('li').find('a').trigger('click');
        })

        $('a.create_survey_wizard_tabs').on('shown.bs.tab', function (e) {
            var count = $(e.target).data('count'); 
            var sessionStorageValue = sessionStorage.getItem('maxtabs') || 1;
            //console.log(count, sessionStorageValue);
            if(count>3 || sessionStorageValue>3){
                $('#save-form-button').removeClass('disabled');
                $('#save-and-close-form-button').removeClass('disabled');
            }

            $('.text-option-inherit').on('change', function(e){
                var newValue = $(this).find('.btn.active input').val();
                var parent = $(this).parent().parent();
                var inheritValue = parent.find('.inherit-edit').data('inherit-value');
                var savedValue = parent.find('.inherit-edit').data('saved-value');
                console.log({
                newValue: newValue,
                parent: parent,
                inheritValue: inheritValue,
                savedValue: savedValue
                })
                if (newValue == 'Y'){
                    parent.find('.inherit-edit').addClass('hide').removeClass('show').val(inheritValue);
                    parent.find('.inherit-readonly').addClass('show').removeClass('hide');
                } else {
                    var inputValue = (savedValue === inheritValue) ? "" : savedValue;
                    parent.find('.inherit-edit').addClass('show').removeClass('hide').val(inputValue);
                    parent.find('.inherit-readonly').addClass('hide').removeClass('show');
                }
            });
        });

        $('#addnewsurvey').on('submit',  function(event){
            event.preventDefault();
            var form = this;

            updateCKfields();
            var data = $(form).serializeArray();
            var uri = $(form).attr('action');
            $.ajax({
                url: uri,
                method:'POST',
                data: data,
                success: function(result){
                if(result.redirecturl != undefined ){
                    window.location.href=result.redirecturl;
                } else {
                    window.location.reload();
                }
                },
                error: function(result){
                console.log({result: result});
                }
            });
            return false;
        });
    });

</script>
