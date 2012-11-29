<div class='header ui-widget-header'><?php $clang->eT("Import Question") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php $clang->eT("Success") ?></div><br />
    <?php $clang->eT("File upload succeeded.") ?><br /><br />
    <?php $clang->eT("Reading file..") ?><br /><br />
    <div class='successheader'><?php $clang->eT("Success") ?></div><br />
    <strong><u><?php $clang->eT("Question import summary") ?></u></strong><br />
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
    <strong><?php $clang->eT("Question import is complete.") ?></strong><br />
    <input type='submit' value='<?php $clang->eT("Go to question") ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid . '/qid/' . $aImportResults['newqid']) ?>', '_top')" />
</div>