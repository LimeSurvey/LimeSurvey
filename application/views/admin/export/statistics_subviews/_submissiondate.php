<?php if (isset($datestamp) && $datestamp == "Y"): ?>
    <div class="panel panel-primary " id="pannel-1">
        <div class="panel-heading">
            <h4 class="panel-title"><?php eT("Submission date"); ?></h4>
        </div>
        <div class="panel-body">
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampE'><?php eT("Equals:"); ?></label>
                <div class="col-sm-5">
                    <?php echo CHtml::textField('datestampE',isset($_POST['datestampE'])?$_POST['datestampE']:'',array('id'=>'datestampE', 'class'=>'popupdate', 'size'=>'12'));?>
                </div>
            </div>
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampG'><?php eT("Later than:");?></label>
                <div class="col-sm-5">
                    <?php echo CHtml::textField('datestampG',isset($_POST['datestampG'])?$_POST['datestampG']:'',array('id'=>'datestampG', 'class'=>'popupdate', 'size'=>'12'));?>
                </div>
            </div>
            <div class='form-group'>
                <label class="col-sm-4 control-label" for='datestampL'><?php eT("Earlier than:");?></label>
                <div class="col-sm-5">
                    <?php echo CHtml::textField('datestampL',isset($_POST['datestampL'])?$_POST['datestampL']:'',array('id'=>'datestampL', 'class'=>'popupdate', 'size'=>'12'));?>
                </div>
            </div>
            <input type='hidden' name='summary[]' value='datestampE' />
            <input type='hidden' name='summary[]' value='datestampG' />
            <input type='hidden' name='summary[]' value='datestampL' />
        </div>
    </div>
    <?php endif; ?>