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
                        'icon' => str_replace($state . '</a>', '</a>', $survey->getRunning()),
                        'state' => $state,
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
}
