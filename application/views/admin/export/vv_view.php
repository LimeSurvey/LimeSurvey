<?php echo CHtml::form(array("admin/export/sa/vvexport/surveyid/{$surveyid}"), 'post', array('id'=>'vvexport'));?>

    <div class='header ui-widget-header'><?php $clang->eT("Export a VV survey file");?></div>
    <ul>
        <li>
            <label for='surveyid'><?php $clang->eT("Export survey");?>:</label>
            <?php echo CHtml::textField('surveyid', $surveyid,array('size'=>10, 'readonly'=>'readonly')); ?>
        </li>
        <li>
            <label for='completionstate'><?php $clang->eT("Export");?>:</label>
            <?php  echo CHtml::dropDownList('completionstate', $selectincansstate, array(
                    'complete' => $clang->gT("Completed responses only"), 
                    'all' => $clang->gT("All responses"),
                    'incomplete' => $clang->gT("Incomplete responses only"),
                    )); ?>
        </li>
        <li>
            <label for='extension' title='<?php $clang->eT("For easy opening in MS Excel, change the extension to 'tab' or 'txt'");?>'><?php $clang->eT("File extension");?>: </label>
            <?php echo CHtml::textField('extension', 'csv',array('size'=>3)); ?>
        </li>
        <li>
            <label for='vvversion' title='<?php $clang->eT("If you want to import survey on old installation or if your survey have problem: use old version (automatically selected if some code are duplicated).");?>'><?php $clang->eT("VV export version");?>: </label>
            <?php  echo CHtml::dropDownList('vvversion', $vvversionseleted, array(
                '2' => $clang->gT("Last VV version"), 
                '1' => $clang->gT("Old VV version"),
                ));; ?>
        <li>
    </ul>
    <p>
        <?php echo CHtml::submitButton($clang->gT('Export results')); ?>
        <?php echo CHtml::hiddenField('subaction','export'); ?>
    </p>
<form>
