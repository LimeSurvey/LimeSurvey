<?php
/**
 * Import question : success
 * This view also is used for the import of a question group
 * @var array $aImportResults
 * @var string $sExtension
 * @var string $sid
 * @var string $gid
 */
$isGroupImport = !isset($gid);
$header = $isGroupImport ? gT("Import question group") : gT("Import question");
$headerSummary = $isGroupImport ? gT("Import question group summary") : gT("Import question summary");
$headerComplete = $isGroupImport ? gT("Question group import is complete.") : gT("Question import is complete.");
?>

<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <div class="jumbotron message-box">
                <h1 class="text-info"><?= $header ?></h1>
                <p class="lead text-info"><?php eT("Success") ?></p>
                <p>
                    <?php eT("File upload succeeded.") ?>
                </p>
                <?php
                $result = '<ul class="list-unstyled">';
                if($isGroupImport) {
                    $result .= '<li>' . gT("Question groups") . ': ' . $aImportResults['groups'] . '</li>';
                }
                $result .= '<li>' . gT("Questions") . ': ' . $aImportResults['questions'] . '</li>' .
                    '<li>' . gT("Subquestions") . ': ' . $aImportResults['subquestions'] . '</li>' .
                    '<li>' . gT("Answers") . ': ' . $aImportResults['answers'] . '</li>';

                if($isGroupImport) {
                    $result .= '<li>' . gT("Conditions") . ': ' . $aImportResults['conditions'] . '</li>';
                }
                if (strtolower($sExtension) == 'csv') {
                    $result .= '<li>' . gT("Label sets") . ': ' . $aImportResults['labelsets'] . ' (' . $aImportResults['labels'] . ')' . '</li>';
                }
                $result .= '<li>' . gT("Question attributes:") . $aImportResults['question_attributes'] . '</li>' .
                    '</ul>';

                $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => $headerSummary,
                    'text' => $result,
                    'type' => 'info',
                    'htmlOptions' => ['class' => 'text-center'],
                ]);
                ?>

                <!-- Warnings -->
                <?php
                if (count($aImportResults['importwarnings']) > 0) {
                    $warnings = '<ul  class="list-unstyled">';
                    foreach ($aImportResults['importwarnings'] as $warning) {
                        $warnings .= '<li>' . $warning . '</li>';
                    }
                    $warnings .= '</ul>';
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'header' => gT("Warnings"),
                        'text' => $warnings,
                        'type' => 'warning',
                        'htmlOptions' => ['class' => 'text-center'],
                    ]);
                }
                ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => $headerComplete,
                    'type' => 'success',
                    'htmlOptions' => ['class' => 'text-center'],
                ]);
                ?>
                <p>
                    <?php if($isGroupImport) : ?>
                        <a href="<?= $this->createUrl('questionGroupsAdministration/view/surveyid/'.$sid.'/gid/'.$aImportResults['newgid']) ?>"
                           class="btn btn-outline-secondary btn-lg" ><?php eT("Go to question group") ?></a>
                    <?php else : ?>
                        <a href="<?php echo $this->createUrl('questionAdministration/view/surveyid/' . $sid . '/gid/' . $gid . '/qid/' . $aImportResults['newqid']) ?>"
                           class="btn btn-outline-secondary btn-lg"><?php eT("Go to question") ?></a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
