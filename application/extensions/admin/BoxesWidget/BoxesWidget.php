<?php

class BoxesWidget extends CWidget
{
    public $model;
    const TYPE_PRODUCT = 0;
    const TYPE_PRODUCT_GROUP = 1;
    const TYPE_LINK = 2;
    public $items = [];
    public $limit = 5;
    public $searchBox = true;
    /**
     * For rendering the switch to decide which view widget is rendered
     * @var $switch bool
     */
    public bool $switch = false;

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
    }

    public function run()
    {
        $boxes = [];
        foreach ($this->items as $item) {
            $item = (object)$item;

            if (isset($item->type) && $item->type == self::TYPE_LINK) {
                $boxes[] = [
                    'link' => $item->link,
                    'type' => self::TYPE_LINK,
                    'icon' => $item->icon ?? '',
                    'text' => $item->text,
                    'external' => $item->external ?? false,
                    'color' => $item->color ?? '',
                ];
            } elseif (isset($item->type) && $item->type == self::TYPE_PRODUCT) {
                $item->model->active = "";

                // Filter state
                if (isset($_GET['active']) && !empty($_GET['active'])) {
                    $item->model->active = $_GET['active'];
                }

                // Set number of page
                App()->user->setState('pageSize', $item->limit);
                $this->limit = $item->limit;

                $surveys = $item->model->search()->getData();
                foreach ($surveys as $survey) {
                    $boxes[] = [
                        'survey' => $survey,
                        'type' => self::TYPE_PRODUCT,
                        'external' => $item->external ?? false,
                        'state' => $survey->getState(),
                        'buttons' => $survey->getButtons(),
                        'link' => App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid),
                    ];
                }
            }
        }
        $enableLoadMoreBtn = !empty($boxes);

        if (!$enableLoadMoreBtn) {
            $boxes[] = [
                'type' => self::TYPE_LINK,
                'link' => App()->createUrl('/surveyAdministration/newSurvey/'),
                'text' => 'Create survey',
                'icon' => 'ri-add-line',
                'color' => '#8146F6',
                'external' => false
            ];
            $boxes[] = [
                'type' => self::TYPE_LINK,
                'link' => App()->createUrl('/admin/surveysgroups/sa/create/'),
                'text' => 'Create survey group',
                'icon' => 'ri-add-line',
                'color' => '#6D748C',
                'external' => false
            ];
        }

        $this->render('boxes', [
            'items' => $boxes,
            'limit' => $this->limit,
            'enableLoadMoreBtn' => $enableLoadMoreBtn
        ]);
    }
}
