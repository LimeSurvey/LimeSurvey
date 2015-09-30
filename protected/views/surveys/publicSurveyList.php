<div class="col-md-4 col-md-offset-4 col-xs-12" style="text-align: center;">
<?php
    echo TbHtml::tag('h1', [], TbHtml::image(App()->baseUrl . Yii::getPathOfAlias('public') . '/images/logo-text.png'));
    $list = '';
    if (!empty($publicSurveys)) {
        $this->widget(TbNav::class, [
            'type' => TbHtml::NAV_TYPE_LIST,
            'items' => array_merge([['label' => "Public surveys"]], array_map(function(\ls\models\Survey $survey) {
                return [
                    'label' => "{$survey->getLocalizedTitle()} ({$survey->primaryKey})",
                    'url' => ['surveys/start', 'id' => $survey->sid, 'lang' => App()->language]
                ];
            }, $publicSurveys))
        ]);
    }

    if (!empty($futureSurveys)) {
        $this->widget(TbNav::class, [
            'type' => TbHtml::NAV_TYPE_LIST,
            'items' => array_merge([['label' => gT("Following survey(s) are not yet active but you can register for them.")]], array_map(function(\ls\models\Survey $survey) {
                return [
                    'label' => "{$survey->getLocalizedTitle()} ({$survey->primaryKey})",
                    'url' => ['surveys/register', 'id' => $survey->sid, 'lang' => App()->language]
                ];
            }, $futureSurveys))
        ]);

    }

?>
</div>
