<?php $this->beginWidget('CActiveForm', array(
    'action' => App()->createUrl('/admin/participants/sa/attributeMap'),
    ), 'post'
); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div class="modal-title h4" id="participant_edit_modal"><?php echo ngT('Add participant to survey|Add participants to survey', $count); ?></div>
    </div>

    <div class="modal-body ">

        <div class='col-sm-4'></div>
        <div class='col-sm-8'>
            <p><?php echo ngT('Add the selected participant to survey.|Add the {n} selected participants to survey.', $count); ?></p>
        </div>

        <!-- Comma separated list -->
        <input type='hidden' name='participant_id' value='<?php echo $participant_id; ?>'/>
        <div class="row ls-space margin top-10 bottom-10">
            <div class='form-group'>
                <label class='control-label col-sm-4'>
                    <?php eT('Survey'); ?>
                </label>
                <div class='col-sm-8'>
                    <select name='survey_id' class='form-control'>
                        <?php foreach ($surveys as $survey): ?>
                            <?php if ($hasGlobalPermission || Permission::model()->hasSurveyPermission($survey->sid, 'tokens', 'update')): ?>
                                <option value='<?php echo $survey->sid; ?>'><?php echo $survey->languagesettings[$survey->language]->surveyls_title; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row ls-space margin top-10 bottom-10">
            <div class='form-group'>
                <label class='control-label col-sm-4'>
                    <?php eT('Display survey tokens after adding?'); ?>
                </label>
                <div class='col-sm-8'>
                    <!--<input type='checkbox' name='redirect' />-->
                    <input name='redirect' type='checkbox' data-size='small' data-on-color='primary' data-off-color='warning' data-off-text='<?php eT('No'); ?>' data-on-text='<?php eT('Yes'); ?>' class='ls-bootstrap-switch' />
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
        <input type="submit" class="btn btn-primary action_save_modal_shareparticipant" value='<?php eT('Next')?>' />
    </div>
<?php $this->endWidget('CActiveForm'); ?>
