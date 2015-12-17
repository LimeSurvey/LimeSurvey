<?php if (isset($datestamp) && $datestamp == "Y"): ?>
    <fieldset id='right'><legend><?php eT("Submission date"); ?></legend><ul><li>
    <label for='datestampE'><?php eT("Equals:"); ?></label>
    <?php echo CHtml::textField('datestampE',isset($_POST['datestampE'])?$_POST['datestampE']:'',array('id'=>'datestampE', 'class'=>'popupdate', 'size'=>'12'));?>
    </li><li><label for='datestampG'><?php eT("Later than:");?></label>
    <?php echo CHtml::textField('datestampG',isset($_POST['datestampG'])?$_POST['datestampG']:'',array('id'=>'datestampG', 'class'=>'popupdate', 'size'=>'12'));?>
    </li><li><label for='datestampL'><?php eT("Earlier than:");?></label>
    <?php echo CHtml::textField('datestampL',isset($_POST['datestampL'])?$_POST['datestampL']:'',array('id'=>'datestampL', 'class'=>'popupdate', 'size'=>'12'));?>
    </li></ul></fieldset>
    <input type='hidden' name='summary[]' value='datestampE' />
    <input type='hidden' name='summary[]' value='datestampG' />
    <input type='hidden' name='summary[]' value='datestampL' />

<?php endif; ?>
