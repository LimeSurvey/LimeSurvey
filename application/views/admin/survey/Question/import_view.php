<div class='header ui-widget-header'><?php eT("Import Question") ?></div>
<div class='messagebox ui-corner-all'>
    <div class='successheader'><?php eT("Success") ?></div><br />
    <?php eT("File upload succeeded.") ?><br /><br />
    <?php eT("Reading file..") ?><br /><br />
    <div class='successheader'><?php eT("Success") ?></div><br />
    <strong><?php eT("Question import summary") ?></strong><br />
    <ul style="text-align:left;">
        <li><?php echo gT("Questions") . ": " . $aImportResults['questions'] ?></li>
        <li><?php echo gT("Subquestions") . ": " . $aImportResults['subquestions'] ?></li>
        <li><?php echo gT("Answers") . ": " . $aImportResults['answers'] ?></li>
<?php
    if (strtolower($sExtension) == 'csv')
    {
?>
        <li><?php echo gT("Label sets") . ": " . $aImportResults['labelsets'] . " (" . $aImportResults['labels'] ?>)</li>
<?php
    }
?>
        <li><?php echo gT("Question attributes:") . $aImportResults['question_attributes'] ?></li>
    </ul>
                <?php if (!empty($aImportResults['importwarnings'])): ?>
                    <div class='warningheader'><?php eT("Warnings");?>:</div>
                    <ul  class="list-unstyled">
                        <?php
                            foreach ($aImportResults['importwarnings'] as $warning)
                            { ?>
                            <li><?php echo $warning; ?></li>
                            <?php
                        } ?>
                    </ul>
                <?php endif; ?>
    <strong><?php eT("Question import is complete.") ?></strong><br />
    <input type='submit' value='<?php eT("Go to question") ?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid . '/qid/' . $aImportResults['newqid']) ?>', '_top')" />
</div>
