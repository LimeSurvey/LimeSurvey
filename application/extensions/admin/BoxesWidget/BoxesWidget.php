<?php

class BoxesWidget extends CWidget
{
    public $model;
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
        $this->model->active = "";

        // Filter state
        if (isset($_GET['active']) && !empty($_GET['active'])) {
            $this->model->active = $_GET['active'];
        }

        // Set number of page
        if (isset($_GET['pageSize'])) {
            App()->user->setState('pageSize', 4);
        }

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
                $surveys = $this->model->search()->getData();
                foreach ($surveys as $survey) {
                    $state = strip_tags($survey->getRunning());
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

        if ($this->searchBox) {
            $this->controller->widget('ext.admin.SearchBoxWidget.SearchBoxWidget', [
                'model' => new Survey('search'),
                'onlyfilter' => true,
                'switch' => App()->request->getPathInfo() == 'admin/index'
            ]);
        }

        $this->render('boxes', [
            'items' => $boxes,
            'boxesbyrow' => $this->boxesbyrow,
            'limit' => $this->limit
        ]);
    }
}
