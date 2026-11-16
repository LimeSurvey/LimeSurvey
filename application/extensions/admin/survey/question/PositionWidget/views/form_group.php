<?php
/**
 * Position Widget Form Group View
 * Display a position selector in form-group style, fit in right accordions
 *
 * @var $aQuestions     A array containing the datas to create the options for each questions
 */
?>

<!-- PositionWidget : form_group -->
<div id='PositionWidget' class='mb-3'>
    <label class="form-label" for='questionposition'><?php eT("Position:"); ?></label>
    <div>
        <select class='form-select <?php echo $this->classes;?>' name='questionposition' id='questionposition'>
            <?php $this->render('_options', array('aQuestions' => $aQuestions)); ?>
        </select>
    </div>
</div>
<!-- end of PositionWidget : form_group -->
