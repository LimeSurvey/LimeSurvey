<!-- test/execute survey -->
<?php
$notActive = $oSurvey->active=='N';
?>

    <div class="d-inline-flex">
        <?php
        $this->widget('ext.ButtonWidget.ButtonWidget', [
            'name' => $notActive ? 'ls-preview-button' : 'ls-run-button',
            'id' => $notActive ? 'ls-preview-button' : 'ls-run-button',
            'text' => $notActive ? gT('Preview survey') : gT('Run survey'),
            'icon' => $notActive ? 'fa fa-eye' : 'fa fa-play',
            'menu' => count($surveyLanguages) > 1,
            'link' => Yii::App()->createUrl(
                "survey/index",
                array('sid' => $surveyid, 'newtest' => "Y", 'lang' => $oSurvey->language)
            ),
            'htmlOptions' => [
                'class' => 'btn btn-secondary btntooltip',
                'role' => 'button',
                'accesskey' => 'd',
                'target' => '_blank',
            ],
        ]); ?>
        <?php if (count($surveyLanguages) > 1): ?>
        <ul class="dropdown-menu" style="min-width : 252px;">
            <?php foreach ($surveyLanguages as $languageCode => $languageName): ?>
                <li>
                    <a class="dropdown-item" target='_blank' href='<?php echo Yii::App()->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$languageCode));?>'>
                        <?php echo $languageName; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

<?php if($hasSurveyContentUpdatePermission): ?>
    <!-- Preview group -->
    <div class="d-inline-flex">
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'ls-group-preview-button',
        'id' => 'ls-group-preview-button',
        'text' => gT('Preview question group'),
        'icon' => 'fa fa-eye',
        'menu' => count($surveyLanguages) > 1,
        'link' => Yii::App()->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"),
        'htmlOptions' => [
            'class' => 'btn btn-secondary btntooltip',
            'role' => 'button',
            'target' => '_blank',
        ],
    ]); ?>
    <?php if (count($surveyLanguages) > 1) : ?>
            <ul class="dropdown-menu" style="min-width : 252px;">
                <?php foreach ($surveyLanguages as $languageCode => $languageName) : ?>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?php echo Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $languageCode); ?>" >
                            <?php echo $languageName; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

    <?php endif; ?>
    </div>
<?php endif; ?>

