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
    eT('One or more failed send attempts of emails after participants completed a survey.') ?>
</p>
<p>
    <?php
    eT('Affected surveys') ?>:
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

