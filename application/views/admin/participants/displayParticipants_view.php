
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "admin/participantdisplay.js" ?>" type="text/javascript"></script>
<div id ="search" style="display:none">
    <?php
    $optionsearch = array('' => gT("Select..."),
        'firstname' => gT("First name"),
        'lastname' => gT("Last name"),
        'email' => gT("Email"),
        'blacklisted' => gT("Blacklisted"),
        'surveys' => gT("Survey links"),
        'survey' => gT("Survey name"),
        'language' => gT("Language"),
        'owner_uid' => gT("Owner ID"),
        'owner_name' => gT("Owner name"));
    $optioncontition = array('' =>  gT("Select..."),
        'equal' =>gT("Equals"),
        'contains' =>gT("Contains"),
        'beginswith' =>gT("Begins with"),
        'notequal' => gT("Not equal"),
        'notcontains' => gT("Does not contain"),
        'greaterthan' => gT("Greater than"),
        'lessthan' => gT("Less than"));
    if (isset($allattributes) && count($allattributes) > 0) // Add attribute names to select box
    {
        echo "<script type='text/javascript'> optionstring = '";
        foreach ($allattributes as $key => $value)
        {
            $optionsearch[$value['attribute_id']] = $value['defaultname'];
            echo "<option value=" . $value['attribute_id'] . ">" . $value['defaultname'] . "</option>";
        }
        echo "';</script>";
    }
    ?>
    <table id='searchtable'>
        <tr>
            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $optionsearch); ?></td>
            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $optioncontition); ?></td>
            <td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
            <td>&nbsp;<span class='icon-add text-success' id="addbutton" alt='<?php eT("Add search condition"); ?>'></span></td>
        </tr>
    </table>
    <br/>


</div>
<br/>
    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
        <div class="row">

        </div>
        <div class="row">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'list_central_participants',
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $model->columns,
                'rowHtmlOptionsExpression' => '["data-participant_id" => $data->participant_id ]',
                'filter'=>$model,
                'htmlOptions' => array('class'=> 'table-responsive'),
                'itemsCssClass' => 'table table-responsive table-striped',
                'afterAjaxUpdate' => 'bindButtons',
                'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeParticipantView',
                                $pageSizeParticipantView,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                            ),
                    ));
                ?>
            </div>
    </div>
</div>
<div id="pager"></div>
<div id="fieldnotselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select a field"); ?>
    </p>
</div>
<div id="conditionnotselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select a condition"); ?>
    </p>
</div>
<div id="norowselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select at least one participant"); ?>
    </p>
</div>
<div id="shareform" title="<?php eT("Share") ?>" style="display:none">
  <div class='popupgroup'>
    <p>
<?php eT("User with whom the participants are to be shared"); ?></p>
    <p>
        <?php

        $options[''] = gT("Select...");
        $options[0]  = gT("All users");

        foreach ($names as $row)
        {
            if (!(Yii::app()->session['loginID'] == $row['uid']))
            {
                $options[$row['uid']] = $row['full_name'];
            }
        }
        echo CHtml::dropDownList('shareuser', 'id="shareuser"', $options);
        ?>
    </p>
  </div>
  <div class='popupgroup'>
    <p>
<?php eT("Allow this user to edit these participants"); ?>
    </p>
    <p><?php
$data = array(
    'id' => 'can_edit',
    'value' => 'TRUE',
    'style' => 'margin:10px',
);
echo CHtml::checkBox('can_edit', TRUE, $data);
?><input type="hidden" name="can_edit" id="can_edit" value='TRUE'>
    </p>
  </div>
</div>
<!--<div id="addsurvey" title="addsurvey" style="display:none">-->

<!-- Add To Survey Popup Window -->
<div id="client-script-return-msg" style="display:none">
    <?php echo CHtml::form(array("admin/participants/sa/attributeMap"), 'post', array('id'=>'addsurvey','name'=>'addsurvey','class'=>'form-horizontal')); ?>
        <input type="hidden" name="participant_id" id="participant_id" value=""></input>
        <input type="hidden" name="count" id="count" value=""></input>
        <fieldset class='popupgroup'>
            <legend><?php eT("Participants") ?></legend>
            <div id='allinview' style='display: none'><?php eT("Add all participants in your current list to a survey.") ?></div>
            <div id='selecteditems' style='display: none'><?php eT("Add the selected participants to a survey.") ?></div>
            <br />
        </fieldset>
        <fieldset class='popupgroup'>
		  <legend>
            <?php eT("Survey"); ?>
          </legend>
          <p>
            <?php
            if (!empty($tokensurveynames))
            {
                //$option[''] = gT("Select...");
                foreach ($tokensurveynames as $row)
                {
                    $option[$row['surveyls_survey_id']] = $row['surveyls_title'];
                }
                echo CHtml::listBox('survey_id', 'id="survey_id"', $option, array('class'=>'form-control', 'size'=>8));
            }
            ?>
          </p><br />
        </fieldset>
        <fieldset class='popupgroup'>
          <legend>
            <?php eT("Options") ?>
          </legend>
        <div class='form-group'>
            <label class='control-label col-sm-8' for='redirect'><?php eT("Display survey participants after adding?"); ?></label>
            <div class='col-sm-4'>
                <?php
                echo CHtml::checkBox('redirect', TRUE,  array(
                    'id' => 'redirect',
                    'value' => 'TRUE',
                    'class' => '',
                ));
                ?>
            </div>
        </div>

        </fieldset>
    </form>
</div>
<div id="notauthorised" title="notauthorised" style="display:none">
    <p><?php eT("You do not have the permission to edit this participant."); ?></p>
</div>
