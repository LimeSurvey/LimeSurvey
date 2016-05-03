<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Export a VV survey file");?></h3>
        <div class="row">
            <div class="col-lg-12 content-right">


<?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id'=>'vvexport'));?>

    <ul class="list-unstyled">
        <li>
            <label for='surveyid'><?php eT("Export survey");?>:</label>
            <?php echo CHtml::textField('surveyid', $surveyid,array('size'=>10, 'readonly'=>'readonly')); ?>
        </li>
        <li>
            <label for='completionstate'><?php eT("Export");?>:</label>
            <?php  echo CHtml::dropDownList('completionstate', $selectincansstate, array(
                    'complete' => gT("Completed responses only",'unescaped'),
                    'all' => gT("All responses",'unescaped'),
                    'incomplete' => gT("Incomplete responses only",'unescaped'),
                    ), array('class'=>'form-control')); ?>
        </li>
        <li>
            <label for='extension' title='<?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'");?>'><?php eT("File extension");?>: </label>
            <?php echo CHtml::textField('extension', 'csv',array('size'=>3)); ?>
        </li>
        <li>
            <label for='vvversion' title='<?php eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated).");?>'><?php eT("VV export version");?>: </label>
            <?php  echo CHtml::dropDownList('vvversion', $vvversionseleted, array(
                '2' => gT("Last VV version",'unescaped'),
                '1' => gT("Old VV version",'unescaped'),
                ), array('class'=>'form-control'));; ?>
        <li>
    </ul>
    <p>
        <?php echo CHtml::submitButton(gT('Export results','unescaped'), array('class'=>'btn btn-default hidden')); ?>
        <?php echo CHtml::hiddenField('subaction','export'); ?>
    </p>
<form>
</div></div></div>
