<?php $this->beginWidget('CActiveForm', array(
    'action' => App()->createUrl('/admin/participants/sa/attributeMap'),
    ), 'post'
); ?>
    <div class="modal-header">
        <h5 class="modal-title" id="participant_edit_modal"><?php echo ngT('Add participant to survey|Add participants to survey', $count); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body ">

        <div class='col-md-4'></div>
        <div class='col-md-8'>
            <p><?php echo ngT('Add the selected participant to survey.|Add the {n} selected participants to survey.', $count); ?></p>
        </div>

        <!-- Comma separated list -->
        <input type='hidden' name='participant_id' value='<?php echo $participant_id; ?>'/>
        <div class="row ls-space margin top-10 bottom-10">
            <div class='mb-3'>
                <label class='form-label col-md-4'>
                    <?php eT('Survey'); ?>
                </label>
                <div class='col-md-8'>
                    <select name='survey_id' class='form-select'>
                        <?php foreach ($surveys as $survey): ?>
                            <?php if ($hasGlobalPermission || Permission::model()->hasSurveyPermission($survey->sid, 'tokens', 'update')): ?>
                                <option value='<?php echo $survey->sid; ?>'><?php echo $survey->languagesettings[$survey->language]->surveyls_title; ?> (<?php echo $survey->sid; ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10 bottom-10">
            <div class='mb-3'>
                <label class='form-label col-md-4'>
                    <?php eT('Display survey participant list after addition?'); ?>
                </label>
                <div class='col-md-8'>
                    <?php App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => "redirect",
                        'ariaLabel'    => gT('Display survey participant list after addition'),
                        'checkedOption' => "0",
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT('Cancel') ?></button>
        <button role="button" type="submit" class="btn btn-primary action_save_modal_shareparticipant">
            <?php eT('Add')?>
        </button>
    </div>
<?php $this->endWidget('CActiveForm'); ?>
