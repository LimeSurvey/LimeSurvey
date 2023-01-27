<?php
/**
 * Display the result of the exportation
 *
 *
 * @var array $aImportResults
 * @var string $sExtension
 * @var int $surveyid
 */
?>
<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-12">
            
                <!-- Jumbotron -->
                <div class="jumbotron message-box">
                    <h1 class="text-info"><?php eT("Import question group") ?></h1>
                    <p class="lead text-info"><?php eT("Success") ?></p>
                    <p>
                        <?php eT("File upload succeeded.") ?>
                    </p>
                    <!-- results -->
                    <?php
                    $result = '<ul class="list-unstyled">' .
                        '<li>' . gT("Question groups") . ': ' . $aImportResults['groups'] . '</li>' .
                        '<li>' . gT("Questions") . ': ' . $aImportResults['questions'] . '</li>' .
                        '<li>' . gT("Subquestions") . ': ' . $aImportResults['subquestions'] . '</li>' .
                        '<li>' . gT("Answers") . ': ' . $aImportResults['answers'] . '</li>' .
                        '<li>' . gT("Conditions") . ': ' . $aImportResults['conditions'] . '</li>';
                    if (strtolower($sExtension) == 'csv') {
                        $result .= '<li>' . gT("Label sets") . ': ' . $aImportResults['labelsets'] . ' (' . $aImportResults['labels'] . ')' . '</li>';
                    }
                    $result .= '<li>' . gT("Question attributes:") . $aImportResults['question_attributes'] . '</li>' .
                        '</ul>';

                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'header' => gT("Question group import summary"),
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
                        'header' => gT("Question group import is complete."),
                        'type' => 'success',
                        'htmlOptions' => ['class' => 'text-center'],
                    ]);
                    ?>
                    <!-- button -->
                    <p>
                        <a href="<?= $this->createUrl('questionGroupsAdministration/view/surveyid/'.$surveyid.'/gid/'.$aImportResults['newgid']) ?>"
                           class="btn btn-outline-secondary btn-lg" ><?php eT("Go to question group") ?></a>
                    </p>
                </div>                                 
        </div>
    </div>
</div>
