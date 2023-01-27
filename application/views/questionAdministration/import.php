<?php
/**
 * Import question : success
 * @var array $aImportResults
 * @var string $sExtension
 * @var string $sid
 * @var string $gid
 */

?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-12 content-right">
            <div class="jumbotron message-box">
                <h1 class="text-info"><?php eT("Import question") ?></h1>
                <p class="lead text-info"><?php eT("Success") ?></p>
                <p>
                    <?php eT("File upload succeeded.") ?>
                </p>
                <?php
                $result = '<ul class="list-unstyled">' .
                    '<li>' . gT("Questions") . ': ' . $aImportResults['questions'] . '</li>' .
                    '<li>' . gT("Subquestions") . ': ' . $aImportResults['subquestions'] . '</li>' .
                    '<li>' . gT("Answers") . ': ' . $aImportResults['answers'] . '</li>';
                if (strtolower($sExtension) == 'csv') {
                    $result .= '<li>' . gT("Label sets") . ': ' . $aImportResults['labelsets'] . ' (' . $aImportResults['labels'] . ')' . '</li>';
                }
                $result .= '<li>' . gT("Question attributes:") . $aImportResults['question_attributes'] . '</li>' .
                    '</ul>';

                $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => gT("Question import summary"),
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
                    'header' => gT("Question import is complete."),
                    'type' => 'success',
                    'htmlOptions' => ['class' => 'text-center'],
                ]);
                ?>
                <p>
                    <a href="<?php echo $this->createUrl('questionAdministration/view/surveyid/' . $sid . '/gid/' . $gid . '/qid/' . $aImportResults['newqid']) ?>" class="btn btn-outline-secondary btn-lg"><?php eT("Go to question") ?></a>
                </p>
            </div>
        </div>
    </div>
</div>
