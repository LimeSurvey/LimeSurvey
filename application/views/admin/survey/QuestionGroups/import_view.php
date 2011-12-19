<div class='header ui-widget-header'><?php echo $clang->gT("Import question group") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php $clang->gT("Success") ?></div>&nbsp;<br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />
    <?php echo $clang->gT("Reading file..") ?> <br />
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <strong><u><?php $clang->gT("Question group import summary") ?></u></strong><br />
    <ul style="text-align:left;">
        <li><?php echo $clang->gT("Groups") .": " .$aImportResults['groups'] ?></li>
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
     </ul>
     <strong><?php echo $clang->gT("Question group import is complete.") ?></strong><br />
     <input type='submit' value="<?php echo $clang->gT("Go to question group") ?>" onclick="window.open('<?php echo $this>createUrl('admin/survey/view/'.$surveyid.'/'.$aImportResults['newgid']) ?>')" />
</div><br />
