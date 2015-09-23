<div class="row">
    <div class="col-md-12">
        <?php
        $this->pageTitle = gT("Deactivate survey");
        echo TbHtml::tag('h2', [], gT("Warning"));
        echo TbHtml::tag('h3', [], gT("READ THIS CAREFULLY BEFORE PROCEEDING"));
        ?>
        <ul>
            <li><?php eT("No responses are lost.");?></li>
            <li><?php eT("No participant information lost.");?></li>
            <li><?php eT("Ability to change of questions, groups and parameters is still limited.");?></li>
            <li><?php eT("An expired survey is not accessible to participants (they only see a message that the survey has expired).");?></li>
            <li><?php eT("It's still possible to perform statistics on responses using LimeSurvey.");?></li>
        </ul>
    </div>
</div>

<?php
echo TbHtml::beginFormTb('horizontal', ["surveys/expire", "id" => $survey->sid]);
echo TbHtml::checkBoxControlGroup('expire', false, [
    'label' => gT("Yes, I want to expire this survey."),
    'required' => true
]);
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton(gT("Expire survey"), [
    'color' => 'primary',
    'disabled' => !$survey->isActive
]);

echo TbHtml::closeTag('div');
echo TbHtml::endForm();
