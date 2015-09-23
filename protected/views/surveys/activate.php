<?php
    echo TbHtml::labelTb(gT("Warning"), ['color' => 'danger']);
    echo TbHtml::help(gT("READ THIS CAREFULLY BEFORE PROCEEDING"));
    eT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.");
    echo TbHtml::openTag('ul');
    foreach([
        gT("Once a survey is activated you can no longer:"),
        gT("Add or delete groups"),
        gT("Add or delete questions"),
        gT("Add or delete subquestions or change their codes")
    ]   as $item) {
        echo TbHtml::tag('li', [], $item);
    }
    echo TbHtml::closeTag('ul');
    eT("The following settings cannot be changed when the survey is active.");
    eT("Please check these settings now, then click the button below.");

    echo TbHtml::beginFormTb('horizontal', ["surveys/activate", 'id' => $survey->sid], 'post');

    echo TbHtml::well('These checkboxes are all ignored in LS3!@!', ['style' => 'background-color: #f2dede;']);

    echo TbHtml::checkBoxListControlGroup('surveyOptions', ['datestamp'], [
        'anonymized' => gT('Anonymized responses?'),
        'datestamp' => gT('Date stamp?'),
        'ip' => gT('Save IP address?'),
        'referer' => gT("Save referrer URL?"),
        'timings' => gT("Save timings?")
    ], ['label' => 'Options']);

    echo TbHtml::well(gT("Please note that once responses have collected with this survey and you want to add or remove groups/questions or change one of the settings above, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table."));
    echo TbHtml::submitButton(gT("Save / Activate survey"), ['color' => 'primary']);
    echo TbHtml::endForm();
    ?>
</div>