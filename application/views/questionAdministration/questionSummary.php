<?php

/** @var Survey $survey */
/** @var Question $question */
/** @var QuestionTheme $questionTheme */
/** @var boolean $visibilityOverview */
/** @var array<string,array<mixed>> $advancedSettings */
?>

<div class="container-fluid" id="question-overview"<?= empty($visibilityOverview) ? ' style="display:none;"' : '' ?>>
    <?php if ($question->qid !== 0): ?>
        <form>
        <!-- Question summary -->
        <div class="container-center scoped-new-questioneditor">
            <div class="pagetitle h3">
                <?php eT('Question summary'); ?>&nbsp;
                <small>
                    <em><?= $question->title; ?></em>&nbsp;
                    (ID: <?php echo (int) $question->qid;?>)
                </small>
            </div>
            <div class="row">
                <?php $this->renderPartial(
                    "summary",
                    [
                        'question'         => $question,
                        'questionTheme'    => $questionTheme,
                        'answersCount'      => count($question->answers),
                        'subquestionsCount' => count($question->subquestions),
                        'advancedSettings'  => $advancedSettings
                    ]
                ); ?>
            </div>
            <?php if (Permission::model()->hasSurveyPermission($survey->sid, 'surveycontent', 'update')): ?>
                <div id="survey-action-title" class="pagetitle h3"><?php eT('Question quick actions'); ?></div>
                <div class="row welcome survey-action">
                    <div class="col-lg-12 content-right">

                        <!-- create question in this group -->
                        <div class="col-lg-3">
                            <div class="panel panel-primary <?php if ($survey->isActive) { echo 'disabled'; } else { echo 'panel-clickable'; } ?>" id="panel-1" data-url="<?php echo $this->createUrl('questionAdministration/create/surveyid/'.$survey->sid.'/gid/'.$question->gid); ?>">
                                <div class="panel-heading">
                                    <div class="panel-title h4"><?php eT("Add new question to group");?></div>
                                </div>
                                <div class="panel-body">
                                    <span class="icon-add text-success"  style="font-size: 3em;"></span>
                                    <p class='btn-link'>
                                            <?php eT("Add new question to group");?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </form>
    <?php endif; ?>
</div>
