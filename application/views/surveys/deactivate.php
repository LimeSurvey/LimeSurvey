    <div class="row">
    <div class="col-md-12">
        <?php
            $this->pageTitle = gT("Deactivate survey");
            echo TbHtml::tag('h2', [], gT("Warning"));
            echo TbHtml::tag('h3', [], gT("READ THIS CAREFULLY BEFORE PROCEEDING"));
        ?>
        <ul>
            <li><?php eT("All responses are not accessible anymore with LimeSurvey.");?> <?php echo gT("Your response table will be renamed to:")." {$survey->dbConnection->tablePrefix}old_survey_{$survey->sid}_" . date('Ymd'); ?></li>
            <li><?php eT("All participant information is lost.");?></li>
            <li><?php eT("A deactivated survey is not accessible to participants (only a message appears that they are not permitted to see this survey).");?></li>
            <li><?php eT("All questions, groups and parameters are editable again.");?></li>
            <li><?php
                echo TbHtml::link(gT("You should export your responses before deactivating."), ['admin/export', "sa" => "exportresults", "surveyid" => $survey->sid], [
                    'title' => gT("Export survey results")
                ]);
            ?>
            </li>
        </ul>
    </div>
    </div>

<?php
    echo TbHtml::beginFormTb('horizontal', ["surveys/deactivate", "id" => $survey->sid]);
    echo TbHtml::checkBoxControlGroup('deactivate', false, [
        'label' => gT("Yes, I want to deactivate this survey."),
        'required' => true
    ]);
    echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
    echo TbHtml::submitButton(gT("Deactivate survey"), [
        'color' => 'primary',
        'disabled' => !$survey->isActive
    ]);

    echo TbHtml::closeTag('div');
    echo TbHtml::endForm();
