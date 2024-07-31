<?php

class BoxesWidget extends CWidget
{
    const TYPE_PRODUCT = 0;
    const TYPE_PRODUCT_GROUP = 1;
    const TYPE_LINK = 2;
    public $items = [];
    public $limit = 3;
    public $boxesbyrow = 4;
    public $searchBox = true;

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/BoxesWidget/assets/boxes-widget.js',
            CClientScript::POS_END
        );

        App()->getClientScript()->registerCssFile(
            App()->getConfig("extensionsurl") . 'admin/BoxesWidget/assets/boxes-widget.css'
        );
    }

    public function run()
    {
        $boxes = [];
        foreach ($this->items as $item) {
            $item = (object)$item;

            if ($item->type == self::TYPE_LINK) {
                $boxes[] = [
                    'link' => $item->link,
                    'type' => self::TYPE_LINK,
                    'icon' => $item->icon ?? '',
                    'text' => $item->text,
                    'external' => $item->external ?? false,
                    'color' => $item->color ?? '',
                ];
            } elseif ($item->type == self::TYPE_PRODUCT) {
                if ($state = App()->request->getQuery('state')) {
                    $surveys = $item->model->findAll(
                        'active = :active Limit :limit',
                        array(':active' => $state, ':limit' => $item->limit)
                    );
                } else {
                    $surveys = $item->model->findAll(
                        '1 Limit :limit',
                        array(':limit' => $item->limit)
                    );
                }


                foreach ($surveys as $survey) {
                    $state = strip_tags($survey->getRunning());
                    $boxes[] = [
                        'survey' => $survey,
                        'type' => self::TYPE_PRODUCT,
                        'external' => $item->external ?? false,
                        'icon' => $this->getButton($survey),
                        'state' => $survey->getState(),
                        'buttons' => $survey->getButtons(),
                        'link' => App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid),
                    ];
                }
            }
        }

        if ($this->searchBox) {
            $this->render('searchBox');
        }

        $this->render('boxes', [
            'items' => $boxes,
            'boxesbyrow' => $this->boxesbyrow,
            'limit' => $this->limit
        ]);
    }

    public function getButton($survey)
    {
        $permissions = [
            'statistics_read'  => Permission::model()->hasSurveyPermission($survey->sid, 'statistics', 'read'),
            'survey_update'    => Permission::model()->hasSurveyPermission($survey->sid, 'survey', 'update'),
            'responses_create' => Permission::model()->hasSurveyPermission($survey->sid, 'responses', 'create'),
        ];

        if (
            $survey->active === "N"
            && $permissions['survey_update']
            && $survey->groupsCount > 0
            && $survey->getQuestionsCount() > 0
        ) {
            return [
                'title' => gT('Activate'),
                'url' => App()->createUrl("/surveyAdministration/rendersidemenulink/subaction/generalsettings/surveyid/" . $survey->sid),
                'iconClass' => 'ri-check-line'
            ];
        } elseif ($survey->active !== "Y" && $permissions['responses_create']) {
            return [
                'title' => gT('Edit survey'),
                'url' => App()->createUrl("/surveyAdministration/view?iSurveyID=" . $survey->sid),
                'iconClass' => 'ri-edit-line'
            ];
        } elseif ($survey->active === "Y" && $permissions['statistics_read']) {
            return [
                'title' => gT('Statistics'),
                'url' => App()->createUrl("/admin/statistics/sa/simpleStatistics/surveyid/" . $survey->sid),
                'iconClass' => 'ri-line-chart-line',
            ];
        }
    }
}
