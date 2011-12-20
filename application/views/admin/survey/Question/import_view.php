<div class='header ui-widget-header'><?php echo $clang->gT("Import Question") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <?php echo $clang->gT("File upload succeeded.") ?><br /><br />
    <?php echo $clang->gT("Reading file..") ?><br /><br />
    <div class='successheader'><?php echo $clang->gT("Success") ?></div><br />
    <strong><u><?php echo $clang->gT("Question import summary") ?></u></strong><br />
    <ul style="text-align:left;">
        <li><?php echo $clang->gT("Questions") . ": " . $aImportResults['questions'] ?></li>
        <li><?php echo $clang->gT("Subquestions") . ": " . $aImportResults['subquestions'] ?></li>
        <li><?php echo $clang->gT("Answers") . ": " . $aImportResults['answers'] ?></li>
<?php
    if (strtolower($sExtension) == 'csv')
    {
?>
        <li><?php echo $clang->gT("Label sets") . ": " . $aImportResults['labelsets'] . " (" . $aImportResults['labels'] ?>)</li>
<?php
    }
?>
        <li><?php echo $clang->gT("Question attributes:") . $aImportResults['question_attributes'] ?></li>
    </ul>
    <strong><?php echo $clang->gT("Question import is complete.") ?></strong><br />
    <input type='submit' value='<?php echo $clang->gT("Go to question") ?>' onclick="window.open('<?php echo $this->controller->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid . '/qid/' . $aImportResults['newqid']) ?>', '_top')" />
</div>