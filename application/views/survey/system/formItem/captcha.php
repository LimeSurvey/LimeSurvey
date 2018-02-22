<?php
/**
 * For new Captcha system
 */
$labelWidth=isset($labelWidth) ? $labelWidth : 5;
$inputgroupWidth=isset($inputgroupWidth) ? $inputgroupWidth : 7;

?>
<div class='form-group captcha-item'>
    <label class='control-label col-sm-5' for='loadsecurity'><?php echo gT("Please solve the following equation:") ?></label>
    <div class='col-sm-7 load-survey-input input-cell'>
        <div class='input-group'>
            <div class='input-group-addon captcha-widget' >
                  <?php $this->widget('CCaptcha',array(
                      'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
                      'buttonType' => 'button',
                      'buttonLabel' => gt('Reload image','unescaped')
                  )); ?>
            </div>
            <input class='form-control' type='text' size='15' maxlength='15' id='loadsecurity' name='loadsecurity' value='' alt='' required>
        </div>
    </div>
</div>
