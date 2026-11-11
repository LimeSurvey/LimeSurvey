<?php
/**
 * Position Widget General Option View
 * Display a position selector with the same layout as the other options in the question editor general settings
 *
 * @var $aQuestions     A array containing the datas to create the options for each questions
 */
?>

<!-- PositionWidget : general_option -->
<div id='PositionWidget' class="mb-3">
    <div class="question-option-general-setting-block">
        <div class="col-12">
            <label for='questionposition'><?php eT("Position"); ?></label>
        </div>
        <select class='form-select <?php echo $this->classes;?>' name='questionposition' id='questionposition'>
            <?php $this->render('_options', array('aQuestions' => $aQuestions)); ?>
        </select>
    </div>
</div>
<!-- end of PositionWidget : general_option -->
