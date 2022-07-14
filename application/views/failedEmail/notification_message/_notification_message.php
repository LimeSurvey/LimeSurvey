<?php
/**
 * View for the message of the meesage in the notification center
 * @var array $failedEmailSurveyTitles
 *
 *
 */

?>
<p>
    <?php
    eT('Dear survey administrator') ?>,<br>
    <?php
    eT("There were one or more confirmation emails that failed to be sent. Please check the 'Failed email notifications' section in the survey(s) listed below") ?>:
</p>
<ul>
    <?php
    foreach ($failedEmailSurveyTitles as $surveyId => $surveyTitle) : ?>
        <li>
            <?= CHtml::link(
                $surveyTitle,
                Yii::app()->createUrl("failedemail/index/", ['surveyid' => $surveyId])
            ) ?>
        </li>
    <?php
    endforeach; ?>
</ul>

