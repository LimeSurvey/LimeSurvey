<?php

/**
 * Tab Create content
 * This view display the content for the create tab.
 * @var SurveyAdministrationController $this
 * @var Survey $oSurvey
 *
 */
?>
<?php
// TODO: Don't do this
extract($data);

Yii::app()->loadHelper('admin.htmleditor');

$cs = Yii::app()->getClientScript();
$cs->registerPackage('select2-bootstrap');

App()->getClientScript()->registerScript("tabCreate-view-variables", "
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '" . gT("If you are using surveys with a closed participant group or notifications emails you need to set an administrator email address.", 'js') . "'
    var sURLParameters = '';
    var sAddParam = '';
    var standardthemerooturl='" . Yii::app()->getConfig('standardthemerooturl') . "';
    var templaterooturl='" . Yii::app()->getConfig('userthemerooturl') . "';
    var formId = 'addnewsurvey';

", LSYii_ClientScript::POS_BEGIN);
?>
<!-- Form submitted by save button menu bar -->
<?php echo CHtml::form(array('surveyAdministration/insert'), 'post', array('id' => 'addnewsurvey', 'name' => 'addnewsurvey', 'class' => '')); ?>
<!-- Submit button, needs to be the first item for the script to take it -->
<button role="button" class="btn btn-primary btn-primary d-none" type="submit" name="save" id="create_survey_save_and_send" value='insertsurvey'>
    <?php eT("Finish & save"); ?>
</button>

<div class="ls-flex-row align-items-center align-content-center">
    <div class="grow-10 ls-space padding left-10 right-10">
        <div>
            <div class="tab-pane active" id="texts" data-count="1">
                <?php

                /**
                 * @var $aTabTitles
                 * @var $aTabContents
                 * @var $has_permissions
                 * @var $surveyid
                 * @var $surveyls_language
                 */

                if (isset($edittextdata)) {
                    extract($edittextdata);
                }
                $cs = Yii::app()->getClientScript();
                $cs->registerPackage('select2-bootstrap');

                ?>

                <div class="col-4">
                    <div class="mb-3">
                        <label for="surveyTitle"><?= gT('Survey title:') ?></label>
                        <input type="text" class="form-control" name="surveyls_title" id="surveyTitle" required="required" maxlength="200">
                        <div class="form-control-static">
                            <span class='annotation text-danger'><?php echo  gT("Required"); ?> </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="language"><?= gT('Base language:') ?></label>
                        <div>
                            <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                                'asDropDownList' => true,
                                'htmlOptions' => array('style' => "width: 100%"),
                                'data' => isset($listLanguagesCode) ?  $listLanguagesCode : [],
                                'value' => $oSurvey->language,
                                'name' => 'language',
                                'pluginOptions' => array()
                            )); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class=" form-label" for='gsid'><?php eT("Survey group:"); ?></label>
                        <div>
                            <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                                'asDropDownList' => true,
                                'htmlOptions' => array('style' => "width: 100%"),
                                'data' => isset($aSurveyGroupList) ?  $aSurveyGroupList : [],
                                'value' => $oSurvey->gsid ?? 1,
                                'name' => 'gsid',
                                'pluginOptions' => array()
                            )); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="administrator"><?= gT("Administrator:") ?></label>
                        <div>
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'administrator',
                                'checkedOption' => 'default',
                                'selectOptions' => $optionsAdmin ?? [],
                            ]); ?>
                        </div>
                    </div>
                    <div id="conditional-administrator-fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label" for="admin"><?= gT("Administrator name") ?></label>
                            <?php echo CHtml::textField('admin', '', array(
                                'class' => 'form-control',
                                'id' => 'admin',
                                'size' => '50',
                                'maxlength' => '50',
                            )); ?>
                        </div>
                        <div class="mb-3">
                            <label class=" form-label" for='adminemail'><?php eT("Administrator email"); ?></label>
                            <?php echo CHtml::textField('adminemail', '', array(
                                'class' => 'form-control',
                                'id' => 'adminemail',
                                'size' => '50',
                            )); ?>
                        </div>
                    </div>
                    <!-- Submit -->
                    <div class="pt-3">
                        <input id="create-survey-submit" type='submit' class="btn btn-primary col-6" value='<?php eT("Create survey"); ?>' />
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="row">
    <input type="hidden" name="saveandclose" id="submitaddnesurvey" value="1" />
</div>
</form>

<script>
    var updateCKfields = function() {
        var curCKEDITOR = window.CKEDITOR === undefined ? null : window.CKEDITOR;
        if (curCKEDITOR !== null) {
            $('textarea').each(function() {
                var $textarea = $(this);
                if (curCKEDITOR.instances[$textarea.attr('name')] != undefined || curCKEDITOR.instances[$textarea.attr('name')] != null) {
                    $textarea.val(curCKEDITOR.instances[$textarea.attr('name')].getData());
                }
            });
        }
    };

    $(document).on('ready pjax:scriptcomplete', function() {
        sessionStorage.setItem('maxtabs', 1);

        $('#navigation_back').on('click', function(e) {
            e.preventDefault();
            updateCKfields();
            $('#create_survey_tablist').find('.active').prev('li').find('a').trigger('click');
        })
        $('#navigation_next').on('click', function(e) {
            e.preventDefault();
            updateCKfields();
            $('#create_survey_tablist').find('.active').next('li').find('a').trigger('click');
        })

        $('a.create_survey_wizard_tabs').on('shown.bs.tab', function(e) {
            var count = $(e.target).data('count');
            var sessionStorageValue = sessionStorage.getItem('maxtabs') || 1;
            //console.log(count, sessionStorageValue);
            if (count > 3 || sessionStorageValue > 3) {
                $('#save-form-button').removeClass('disabled');
                $('#save-and-close-form-button').removeClass('disabled');
            }

            $('.text-option-inherit').on('change', function(e) {
                var newValue = $(this).find('.btn-check:checked').val();
                var parent = $(this).parent().parent();
                var inheritValue = parent.find('.inherit-edit').data('inherit-value');
                var savedValue = parent.find('.inherit-edit').data('saved-value');
                console.log({
                    newValue: newValue,
                    parent: parent,
                    inheritValue: inheritValue,
                    savedValue: savedValue
                })
                if (newValue == 'Y') {
                    parent.find('.inherit-edit').addClass('d-none').removeClass('show').val(inheritValue);
                    parent.find('.inherit-readonly').addClass('show').removeClass('d-none');
                } else {
                    var inputValue = (savedValue === inheritValue) ? "" : savedValue;
                    parent.find('.inherit-edit').addClass('show').removeClass('d-none').val(inputValue);
                    parent.find('.inherit-readonly').addClass('d-none').removeClass('show');
                }
            });
        });

        $('#addnewsurvey').on('submit', function(event) {
            event.preventDefault();
            // Disable both buttons. Normally there's no need to re-enable them. The 'save-form-button' may already be disabled by it's onclick event.
            $('#create-survey-submit').prop('disabled', true);
            $('#save-form-button').addClass('disabled').attr('onclick', 'return false;');
            var form = this;

            updateCKfields();
            var data = $(form).serializeArray();
            var uri = $(form).attr('action');
            $.ajax({
                url: uri,
                method: 'POST',
                data: data,
                success: function(result) {
                    if (result.redirecturl != undefined) {
                        window.location.href = result.redirecturl;
                    } else {
                        window.location.reload();
                    }
                },
                error: function(result) {
                    console.log({
                        result: result
                    });
                }
            });
            return false;
        });
    });
</script>
