<div class='header ui-widget-header'><?php eT("Import question group") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php eT("Success") ?></div>&nbsp;<br />
    <?php eT("File upload succeeded.") ?><br /><br />
    <?php eT("Reading file..") ?> <br />
    <div class='successheader'><?php eT("Success") ?></div><br />
    <strong><u><?php gT("Question group import summary") ?></u></strong><br />
    <ul style="text-align:left;">
        <li><?php echo gT("Question groups") .": " .$aImportResults['groups'] ?></li>
        <li><?php echo gT("Questions").": ".$aImportResults['questions'] ?></li>
        <li><?php echo gT("Subquestions").": ".$aImportResults['subquestions'] ?></li>
        <li><?php echo gT("Answers").": ".$aImportResults['answers'] ?></li>
        <li><?php echo gT("Conditions").": ".$aImportResults['conditions'] ?></li>
<?php
    if (strtolower($sExtension)=='csv')
    {?>
        <li><?php echo gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")" ?></li>
<?php
    }?>
        <li><?php echo gT("Question attributes:") . $aImportResults['question_attributes'] ?></li>
     </ul><br/>
     <p><strong><?php eT("Question group import is complete.") ?></strong></p><br />
     <input type='submit' value="<?php eT("Go to question group") ?>" onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$aImportResults['newgid']) ?>', '_top')" />
</div><br />
