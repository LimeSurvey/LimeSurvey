<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="participant_edit_modal"><?php echo $model->firstname."&nbsp;".$model->lastname; ?></h4>
</div>
<div class="modal-body form-horizontal">
<?php
    $form = $this->beginWidget(
        'bootstrap.widgets.TbActiveForm',
        array(
            'id' => 'editPartcipantActiveForm',
            'action' => array('admin/participants/sa/editParticipant'),
            'htmlOptions' => array('class' => 'well form-horizontal'), // for inset effect
        )
    );
?>
    <input type="hidden" name="oper" value="<?php echo $editType; ?>" />
    <input type="hidden" name="Participant[participant_id]" value="<?php echo $model->participant_id; ?>" />
    <?php
        echo "<legend>".gT("Basic attributes")."</legend>";
        $baseControlGroupHtmlOptions = array(
             'groupOptions'=> array('class'=>'form-horizontal'),
             'labelOptions'=> array('class'=> 'col-sm-4'),
             'class' => 'col-sm-8',
             'required' => 'required'
        );
        echo $form->textFieldControlGroup($model,'firstname', $baseControlGroupHtmlOptions);
        echo $form->textFieldControlGroup($model,'lastname',$baseControlGroupHtmlOptions);
        echo $form->textFieldControlGroup($model,'email',$baseControlGroupHtmlOptions);
        echo 
        "<div class='row'>
            <div class='col-xs-12'>".gT("Should this user be blacklisted?")."</div>"
      . "</div>
        <div class='text-center'>
            <label class='radio-inline'>"
             . "<input name=\"Participant[blacklisted]\" id=\"Participant_blacklisted\" type=\"radio\" value=\"Y\" "
                .($model->blacklisted == "Y" ? "checked" : "")." />"
             . gT("Yes")."
            </label>
            <label class='radio-inline'>"
             . "<input name=\"Participant[blacklisted]\" id=\"Participant_blacklisted\" type=\"radio\" value=\"N\" "
                .($model->blacklisted == "N" ? "checked" : "")." />"
             . gT("No")."
            </label>
        </div>";
        echo "<br/>";
        echo "<br/>";
        echo "<legend>".gT("Custom attributes")."</legend>";
   
        foreach($extraAttributes as $extraAttribute){
            echo $extraAttribute;
        }
    ?>
    <p>&nbsp;</p>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close') ?></button>
    <button type="button" class="btn btn-primary action_save_modal_editParticipant"><?php eT("Save")?></button>
</div>
<?php
$this->endWidget();
?>