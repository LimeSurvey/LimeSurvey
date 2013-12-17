<div class='header ui-widget-header'><?php $clang->eT("Import question group") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php $clang->eT("Success") ?></div>&nbsp;<br />
    <?php $clang->eT("File upload succeeded.") ?><br /><br />
    <?php $clang->eT("Reading file..") ?> <br />
    <div class='successheader'><?php $clang->eT("Success") ?></div><br />
    <strong><u><?php $clang->gT("Question group import summary") ?></u></strong><br />
    <ul style="text-align:left;">
        <li><?php echo $clang->gT("Question groups") .": " .$aImportResults['groups'] ?></li>
        <li><?php echo $clang->gT("Questions").": ".$aImportResults['questions'] ?></li>
        <li><?php echo $clang->gT("Subquestions").": ".$aImportResults['subquestions'] ?></li>
        <li><?php echo $clang->gT("Answers").": ".$aImportResults['answers'] ?></li>
        <li><?php echo $clang->gT("Conditions").": ".$aImportResults['conditions'] ?></li>
<?php
    if (strtolower($sExtension)=='csv')
    {?>
        <li><?php echo $clang->gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")" ?></li>
<?php
    }?>
        <li><?php echo $clang->gT("Question attributes:") . $aImportResults['question_attributes'] ?></li>
     </ul><br/>
     <p><strong><?php $clang->eT("Question group import is complete.") ?></strong></p><br />
     <input type='submit' value="<?php $clang->eT("Go to question group") ?>" onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$aImportResults['newgid']) ?>', '_top')" />
</div><br />
