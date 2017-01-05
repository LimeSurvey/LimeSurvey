<?php
/**
 * Export result view
 */
?>
<script type="text/javascript">
    var sMsgColumnCount = '<?php eT("%s of %s columns selected",'js'); ?>';
</script>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <?php eT("Export results");?>
        <?php
            if (isset($_POST['sql'])) {echo" - ".gT("Filtered from statistics script");}
            if ($SingleResponse)
            {
                echo " - ".sprintf(gT("Single response: ID %s"),$SingleResponse);
            }
        ?>
    </h3>

    <?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/'.$surveyid), 'post', array('id'=>'resultexport', 'class'=>'form-horizontal'));?>
        <div class="row">
            <div class="col-sm-12 content-right">
                <div class="row">
                    <div class="col-sm-12 col-md-6">

                        <!-- Format -->
                        <div class="panel panel-primary" id="pannel-1">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("Format");?>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <!-- Format -->
                                    <label for='export_from' class="col-sm-2 control-label">
                                        <?php eT("Export format:"); ?>
                                    </label>
                                    <div class="col-sm-4">
                                        <?php foreach ($exports as $key => $info): ?>
                                            <?php if (!empty($info['label'])): ?>
                                                <div class="radio">
                                                    <label><input type="radio" name="type" id="<?php echo $key;?>" value="<?php echo $key;?>" <?php if($info['label']=='CSV'){ echo 'checked';}?>><?php echo $info['label'];?></label>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                                </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Range -->
                        <div class="panel panel-primary" id="pannel-2" <?php  if ($SingleResponse) { echo 'style="display:none"';} ?> >
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("Range");?>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">

                                    <!-- From -->
                                    <label for='export_from' class="col-sm-2 control-label">
                                        <?php eT("From:"); ?>
                                    </label>
                                    <div class="col-sm-2">
                                        <input
                                            min="<?php echo $min_datasets; ?>"
                                            max="<?php echo $max_datasets; ?>"
                                            step="1"
                                            type="number"
                                            value="<?php echo $min_datasets; ?>"
                                            name="export_from"
                                            id="export_from"
                                            class="form-control"
                                        />
                                    </div>

                                    <!-- To -->
                                    <label for='export_to' class="col-sm-1 control-label">
                                        <?php eT("to:"); ?>
                                    </label>
                                    <div class="col-sm-2">
                                        <input
                                            min="<?php echo $min_datasets; ?>"
                                            max="<?php echo $max_datasets; ?>"
                                            step="1"
                                            type="number"
                                            value="<?php echo $max_datasets; ?>"
                                            name="export_to"
                                            id="export_to"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General -->
                        <div class="panel panel-primary" id="pannel-3">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("General"); ?>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for='completionstate' class="col-sm-4 control-label"><?php eT("Completion state:");?></label>

                                    <div class="col-sm-4">
                                        <select name='completionstate' id='completionstate' class='form-control'>
                                            <option value='complete' <?php echo $selecthide;?>><?php eT("Completed responses only");?></option>
                                            <option value='all' <?php echo $selectshow;?>><?php eT("All responses");?></option>
                                            <option value='incomplete' <?php echo $selectinc;?>><?php eT("Incomplete responses only");?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for='exportlang' class="col-sm-4 control-label" >
                                        <?php eT("Export language:"); ?>
                                    </label>
                                    <div class='col-sm-4'>
                                        <?php echo CHtml::dropDownList('exportlang', null, $aLanguages, array('class'=>'form-control')); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Heading -->
                        <div class="panel panel-primary" id="pannel-4">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("Headings");?>
                                </h4>
                            </div>
                            <div class="panel-body">

                                <!-- Headers -->
                                <div class="form-group">
                                    <div class="btn-group col-sm-12" data-toggle="buttons">
                                        <?php foreach($headexports as $type=>$headexport):?>
                                            <label class="btn btn-default <?php if($headexport['checked']=='checked'){ echo 'active';}?>">
                                                <input
                                                    value="<?php echo $type; ?>"
                                                    id="headstyle-<?php echo $type; ?>"
                                                    type="radio"
                                                    name="headstyle"
                                                    <?php if($headexport['checked']=='checked'){ echo 'checked';} ?>
                                                />
                                                <?php echo $headexport['label'];?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Convert spaces -->
                                <div class="form-group">
                                    <label class="col-sm-6 control-label" for='headspacetounderscores'>
                                        <?php eT("Convert spaces in question text to underscores:"); ?>
                                    </label>
                                    <div class='col-sm-1'>
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'headspacetounderscores',
                                            'id'=>'headspacetounderscores',
                                            'value' => 0,
                                            'onLabel'=>gT('On'),
                                            'offLabel' => gT('Off')));
                                        ?>
                                    </div>
                                </div>

                                <!-- Text abbreviated-->
                                <div class="form-group">
                                    <label class="col-sm-6 control-label" for='abbreviatedtext'>
                                        <?php eT("Text abbreviated:"); ?>
                                    </label>
                                    <div class='col-sm-1'>
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'abbreviatedtext',
                                            'id'=>'abbreviatedtext',
                                            'value' => 0,
                                            'onLabel'=>gT('On'),
                                            'offLabel' => gT('Off')));
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for='abbreviatedtextto' class="col-sm-6 control-label">
                                        <?php eT("Number of characters:"); ?>
                                    </label>
                                    <div class="col-sm-2">
                                        <input
                                            min="1"
                                            step="1"
                                            type="number"
                                            value="15"
                                            name="abbreviatedtextto"
                                            id="abbreviatedtextto"
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                                <!-- Use Expression Manager code-->
                                <div class="form-group">
                                    <label class="col-sm-6 control-label" for='emcode'>
                                        <?php eT("Use Expression Manager code:"); ?>
                                    </label>
                                    <div class='col-sm-1'>
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'emcode',
                                            'id'=>'emcode',
                                            'value' => 0,
                                            'onLabel'=>gT('On'),
                                            'offLabel' => gT('Off')));
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for='codetextseparator' class="col-sm-6 control-label">
                                        <?php eT("Code/text separator:"); ?>
                                    </label>
                                    <div class="col-sm-2">
                                        <input
                                            size="4"
                                            type="text"
                                            value=". "
                                            name="codetextseparator"
                                            id="codetextseparator"
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="panel panel-primary" id="pannel-5">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("Responses");?>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <!-- Answer codes / Full answers -->
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default">
                                        <input
                                            name="answers"
                                            value="short"
                                            type="radio"
                                            id="answers-short"
                                        />
                                        <?php eT("Answer codes");?>
                                    </label>

                                    <label class="btn btn-default active">
                                        <input
                                            name="answers"
                                            value="long"
                                            type="radio"
                                            checked='checked'
                                            id="answers-long"
                                            autofocus="true"
                                        />
                                        <?php eT("Full answers");?>
                                    </label>
                                </div>

                                <!-- Responses  -->
                                <div class="form-group">
                                    <br/>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo CHTML::checkBox('converty',false,array('value'=>'Y','id'=>'converty'));
                                    echo '&nbsp;'.CHTML::label(gT("Convert Y to:"),'converty');?>
                                    <?php echo CHTML::textField('convertyto','1',array('id'=>'convertyto','size'=>'3','maxlength'=>'1')); ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo CHTML::checkBox('convertn',false,array('value'=>'Y','id'=>'convertn'));
                                    echo '&nbsp;'.CHTML::label(gT("Convert N to:"),'convertn');?>
                                    <?php echo CHTML::textField('convertnto','2',array('id'=>'convertnto','size'=>'3','maxlength'=>'1')); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-sm-12 col-md-6">

                        <!-- Column control -->
                        <div class="panel panel-primary" id="pannel-6">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <?php eT("Columns");?>
                                </h4>
                            </div>
                            <div class="panel-body">
                                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                                <?php if ($SingleResponse): ?>
                                    <input type='hidden' name='response_id' value="<?php echo $SingleResponse;?>" />
                                <?php endif; ?>
                                <label for='colselect' class="col-sm-3 control-label">
                                    <?php eT("Select columns:");?>
                                </label>
                                <div class="col-sm-9">
                                <?php
                                    echo CHtml::listBox('colselect[]',array_keys($aFields),$aFields,array('multiple'=>'multiple','size'=>'20','options'=>$aFieldsOptions, 'class'=>'form-control'));
                                ?>
                                </div>
                                <div class="col-sm-8 col-sm-offset-4">
                                    <br/>
                                    <strong id='columncount'>&nbsp;</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Token control -->
                        <?php if ($thissurvey['anonymized'] == "N" && tableExists("{{tokens_$surveyid}}") && Permission::model()->hasSurveyPermission($surveyid,'tokens','read')): ?>
                            <div class="panel panel-primary" id="pannel-7">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <?php eT("Token control");?>
                                    </h4>
                                </div>
                                <div class="panel-body">
                                    <div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                                        <?php eT('Your survey can export associated token data with each response. Select any additional fields you would like to export.'); ?>
                                    </div>

                                    <label for='attribute_select' class="col-sm-4 control-label">
                                        <?php eT("Choose token fields:");?>
                                    </label>
                                    <div class="col-sm-8">
                                        <select name='attribute_select[]' multiple size='20' class="form-control" id="attribute_select">
                                            <option value='first_name' id='first_name'><?php eT("First name");?></option>
                                            <option value='last_name' id='last_name'><?php eT("Last name");?></option>
                                            <option value='email_address' id='email_address'><?php eT("Email address");?></option>

                                            <?php $attrfieldnames=getTokenFieldsAndNames($surveyid,true);
                                            foreach ($attrfieldnames as $attr_name=>$attr_desc)
                                            {
                                                echo "<option value='$attr_name' id='$attr_name' />".$attr_desc['description']."</option>\n";
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif;?>

                    </div>
                </div>
            </div>
        </div>
          <input type='submit' class="btn btn-default hidden" value='<?php eT("Export data");?>' id='exportresultsubmitbutton' />
    </form>
</div>
