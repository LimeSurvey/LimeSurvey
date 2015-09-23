<?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id'=>'vvexport'));?>

    <div class='header ui-widget-header'><?php eT("Export a VV survey file");?></div>
    <ul>
        <li>
            <label for='surveyid'><?php eT("Export survey");?>:</label>
            <?php echo CHtml::textField('surveyid', $surveyid,array('size'=>10, 'readonly'=>'readonly')); ?>
        </li>
        <li>
            <label for='completionstate'><?php eT("Export");?>:</label>
            <?php  echo CHtml::dropDownList('completionstate', $selectincansstate, array(
                    'complete' => gT("Completed responses only"), 
                    'all' => gT("All responses"),
                    'incomplete' => gT("Incomplete responses only"),
                    )); ?>
        </li>
        <li>
            <label for='extension' title='<?php eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'");?>'><?php eT("File extension");?>: </label>
            <?php echo CHtml::textField('extension', 'csv',array('size'=>3)); ?>
        </li>
        <li>
            <label for='vvversion' title='<?php eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated).");?>'><?php eT("VV export version");?>: </label>
            <?php  echo CHtml::dropDownList('vvversion', $vvversionseleted, array(
                '2' => gT("Last VV version"), 
                '1' => gT("Old VV version"),
                ));; ?>
        <li>
    </ul>
    <p>
        <?php echo CHtml::submitButton(gT('Export results')); ?>
        <?php echo CHtml::hiddenField('subaction','export'); ?>
    </p>
<form>
